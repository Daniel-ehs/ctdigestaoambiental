<?php
/**
 * Controlador de Inspeções
 * VERSÃO ATUALIZADA COM UPLOAD PARA S3 (MINIO) E OTIMIZAÇÃO DE IMAGENS
 */

// Inclui o autoload do Composer para carregar o AWS SDK, Intervention Image e PhpSpreadsheet
require_once BASE_PATH . '/vendor/autoload.php';
use Aws\S3\S3Client;

use Intervention\Image\ImageManagerStatic as Image;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once BASE_PATH . '/models/Inspecao.php';
require_once BASE_PATH . '/models/Setor.php';
require_once BASE_PATH . '/models/Local.php';
require_once BASE_PATH . '/models/TipoApontamento.php';
require_once BASE_PATH . '/models/Usuario.php';
require_once BASE_PATH . '/models/PlanoAcao.php';
require_once BASE_PATH . '/models/Empresa.php';

class InspecaoController
{
    private $inspecaoModel;
    private $setorModel;
    private $localModel;
    private $tipoApontamentoModel;
    private $usuarioModel;
    private $planoAcaoModel;
    private $empresaModel;
    private $s3Client; // Novo cliente S3

    public function __construct()
    {
        $this->inspecaoModel = new Inspecao();
        $this->setorModel = new Setor();
        $this->localModel = new Local();
        $this->tipoApontamentoModel = new TipoApontamento();
        $this->usuarioModel = new Usuario();
        $this->planoAcaoModel = new PlanoAcao();
        $this->empresaModel = new Empresa();

        try {
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'region' => getenv('S3_REGION'),
                'endpoint' => getenv('S3_ENDPOINT'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => getenv('S3_KEY'),
                    'secret' => getenv('S3_SECRET'),
                ],
            ]);
        } catch (Exception $e) {
            error_log("Erro ao inicializar o cliente S3: " . $e->getMessage());
            $this->s3Client = null;
        }

        Image::configure(['driver' => 'gd']);
    }

    public function index()
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $tipos = $this->tipoApontamentoModel->listar();
        $empresas = $this->empresaModel->getAll();
        $empresaId = isset($_GET["empresa_id"]) ? intval($_GET["empresa_id"]) : null;
        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';

        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId) {
            $empresaId = $usuarioEmpresaId;
        }

        $filtros = [
            "setor_id" => $_GET["setor_id"] ?? null,
            "local_id" => $_GET["local_id"] ?? null,
            "tipo_id" => $_GET["tipo_id"] ?? null,
            "status" => $_GET["status"] ?? null,
            "data_inicio" => $_GET["data_inicio"] ?? null,
            "data_fim" => $_GET["data_fim"] ?? null,
            "semana_ano" => $_GET["semana_ano"] ?? null,
            "setor_nome" => $_GET["setor_nome"] ?? null,
            "tipo_nome" => $_GET["tipo_nome"] ?? null,
            "empresa_id" => $empresaId,
            "usuario_empresa_id" => $usuarioEmpresaId,
            "usuario_nivel" => $usuarioNivel,
            "termo_busca" => $_GET["busca"] ?? null // NOVO: Capturar termo de busca global
        ];

        $setores = $this->setorModel->listar(true, $filtros['empresa_id']);
        $locais = $this->localModel->listar(true, $filtros['setor_id'], $filtros['empresa_id']);

        $pagina = isset($_GET["pagina"]) ? (int) $_GET["pagina"] : 1;
        $porPagina = 50;
        $totalInspecoes = $this->inspecaoModel->contar($filtros);
        $totalPaginas = ceil($totalInspecoes / $porPagina);
        $inspecoes = $this->inspecaoModel->listar($filtros, $pagina, $porPagina);

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }

        include BASE_PATH . "/views/inspecoes/index.php";
    }

    public function create()
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $setores = $this->setorModel->listar();
        $locais = $this->localModel->listar();
        $tipos = $this->tipoApontamentoModel->listar();
        $responsaveis = $this->usuarioModel->listar();
        $empresas = $this->empresaModel->getAll();

        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }

        include BASE_PATH . "/views/inspecoes/create.php";
    }

    public function store()
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['prazo']) && empty($_POST['prazo'])) {
                $_POST['prazo'] = null;
            }

            $dados = [
                "data_apontamento" => $_POST["data_apontamento"],
                "setor_id" => $_POST["setor_id"],
                "local_id" => $_POST["local_id"],
                "tipo_id" => $_POST["tipo_id"],
                "apontamento" => $_POST["apontamento"],
                "risco_consequencia" => $_POST["risco_consequencia"] ?? null,
                "resolucao_proposta" => $_POST["resolucao_proposta"] ?? null,
                "responsavel" => $_POST["responsavel"],
                "prazo" => $_POST["prazo"],
                "usuario_id" => $_SESSION["user_id"],
                "empresa_id" => $_POST["empresa_id"],
                "foto_antes" => null
            ];

            if (isset($_FILES["foto_antes"]) && $_FILES["foto_antes"]["error"] == 0) {
                if (!$this->s3Client) {
                    setFlashMessage("error", "Serviço de armazenamento não está configurado. Contacte o administrador.");
                    $this->create();
                    return;
                }

                $file = $_FILES["foto_antes"];
                $tempPath = $file['tmp_name'];
                $originalExt = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                $mimeType = mime_content_type($tempPath);

                try {
                    $image = Image::make($tempPath)
                        ->resize(1200, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });

                    $optimizedTempPath = tempnam(sys_get_temp_dir(), 'optimized_image_');
                    $fileName = 'fotos_antes/' . uniqid("inspecao_antes_", true);

                    if (in_array($originalExt, ['jpeg', 'jpg'])) {
                        $image->encode('jpg', 75)->save($optimizedTempPath);
                        $fileName .= ".jpg";
                        $contentType = 'image/jpeg';
                    } elseif ($originalExt === 'png') {
                        $image->encode('png', 90)->save($optimizedTempPath);
                        $fileName .= ".png";
                        $contentType = 'image/png';
                    } else {
                        $image->save($optimizedTempPath);
                        $fileName .= "." . $originalExt;
                        $contentType = $mimeType;
                    }

                    $result = $this->s3Client->putObject([
                        'Bucket' => getenv('S3_BUCKET'),
                        'Key' => $fileName,
                        'SourceFile' => $optimizedTempPath,
                        'ACL' => 'public-read',
                        'ContentType' => $contentType
                    ]);

                    $dados["foto_antes"] = $result['ObjectURL'];

                    unlink($optimizedTempPath);

                } catch (Exception $e) {
                    error_log("Erro ao otimizar ou fazer upload para o S3: " . $e->getMessage());
                    setFlashMessage("error", "Erro ao processar a foto: " . $e->getMessage());
                    $this->create();
                    return;
                }
            }

            if ($this->inspecaoModel->criar($dados)) {
                setFlashMessage("success", "Inspeção criada com sucesso!");
                redirect("index.php?route=inspecoes");
            } else {
                setFlashMessage("error", "Erro ao criar inspeção.");
                $this->create();
            }
        }
    }

    public function view($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $inspecao = $this->inspecaoModel->obterPorId($id);
        if (!$inspecao) {
            setFlashMessage("error", "Inspeção não encontrada.");
            redirect("index.php?route=inspecoes");
        }

        $planos_acao = $this->planoAcaoModel->obterPorInspecaoId($id);

        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';

        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para visualizar esta inspeção.");
            redirect("index.php?route=inspecoes");
            return;
        }

        include BASE_PATH . "/views/inspecoes/view.php";
    }

    public function edit($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $inspecao = $this->inspecaoModel->obterPorId($id);
        if (!$inspecao) {
            setFlashMessage("error", "Inspeção não encontrada.");
            redirect("index.php?route=inspecoes");
        }

        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para editar esta inspeção.");
            redirect("index.php?route=inspecoes");
            return;
        }

        $setores = $this->setorModel->listar();
        $locais = $this->localModel->listar();
        $tipos = $this->tipoApontamentoModel->listar();
        $responsaveis = $this->usuarioModel->listar();
        $empresas = $this->empresaModel->getAll();

        include BASE_PATH . "/views/inspecoes/edit.php";
    }

    public function update($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['prazo']) && empty($_POST['prazo'])) {
                $_POST['prazo'] = null;
            }

            $dados = [
                "data_apontamento" => $_POST["data_apontamento"],
                "setor_id" => $_POST["setor_id"],
                "local_id" => $_POST["local_id"],
                "tipo_id" => $_POST["tipo_id"],
                "apontamento" => $_POST["apontamento"],
                "risco_consequencia" => $_POST["risco_consequencia"] ?? null,
                "resolucao_proposta" => $_POST["resolucao_proposta"] ?? null,
                "responsavel" => $_POST["responsavel"],
                "prazo" => $_POST["prazo"],
                "observacao" => $_POST["observacao"] ?? null,
                "empresa_id" => $_POST["empresa_id"]
            ];

            if (isset($_POST["status"])) {
                $dados["status"] = $_POST["status"];
                $dados["data_conclusao"] = ($_POST["status"] === "Concluído") ? date("Y-m-d H:i:s") : null;
            }

            if ($this->inspecaoModel->atualizar($id, $dados)) {
                setFlashMessage("success", "Inspeção atualizada com sucesso!");
                redirect("index.php?route=inspecoes&action=view&id=" . $id);
            } else {
                setFlashMessage("error", "Erro ao atualizar inspeção.");
                $this->edit($id);
            }
        }
    }

    public function delete($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $inspecao = $this->inspecaoModel->obterPorId($id);
        if (!$inspecao) {
            setFlashMessage("error", "Inspeção não encontrada.");
            redirect("index.php?route=inspecoes");
            return;
        }

        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para excluir esta inspeção.");
            redirect("index.php?route=inspecoes");
            return;
        }

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        try {
            // Tenta excluir a foto antes do S3
            if (!empty($inspecao['foto_antes'])) {
                $key = parse_url($inspecao['foto_antes'], PHP_URL_PATH);
                if ($key) {
                    $this->s3Client->deleteObject([
                        'Bucket' => getenv('S3_BUCKET'),
                        'Key' => ltrim($key, '/')
                    ]);
                }
            }

            // Tenta excluir a foto depois do S3
            if (!empty($inspecao['foto_depois'])) {
                $key = parse_url($inspecao['foto_depois'], PHP_URL_PATH);
                if ($key) {
                    $this->s3Client->deleteObject([
                        'Bucket' => getenv('S3_BUCKET'),
                        'Key' => ltrim($key, '/')
                    ]);
                }
            }

            if ($this->inspecaoModel->excluir($id)) {
                if ($isAjax) {
                    header("Content-Type: application/json");
                    echo json_encode(["success" => true, "message" => "Inspeção excluída com sucesso!"]);
                    exit;
                } else {
                    setFlashMessage("success", "Inspeção excluída com sucesso!");
                    redirect("index.php?route=inspecoes");
                }
            } else {
                if ($isAjax) {
                    header("Content-Type: application/json", true, 500);
                    echo json_encode(["success" => false, "message" => "Erro ao excluir inspeção."]);
                    exit;
                } else {
                    setFlashMessage("error", "Erro ao excluir inspeção.");
                    redirect("index.php?route=inspecoes");
                }
            }
        } catch (\Aws\S3\Exception\S3Exception $e) {
            error_log("Erro ao excluir foto do S3: " . $e->getMessage());
            if ($isAjax) {
                header("Content-Type: application/json", true, 500);
                echo json_encode(["success" => false, "message" => "Erro ao excluir foto do armazenamento: " . $e->getMessage()]);
                exit;
            } else {
                setFlashMessage("error", "Erro ao excluir foto do armazenamento: " . $e->getMessage());
                redirect("index.php?route=inspecoes");
            }
        }
    }

    public function showConcluirForm($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $inspecao = $this->inspecaoModel->obterPorId($id);
        if (!$inspecao) {
            setFlashMessage("error", "Inspeção não encontrada.");
            redirect("index.php?route=inspecoes");
        }

        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';

        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para concluir esta inspeção.");
            redirect("index.php?route=inspecoes");
            return;
        }

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }

        include BASE_PATH . "/views/inspecoes/concluir.php";
    }

    public function concluir($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $inspecaoAtual = $this->inspecaoModel->obterPorId($id);
            if (!$inspecaoAtual) {
                setFlashMessage("error", "Inspeção não encontrada.");
                redirect("index.php?route=inspecoes");
                return;
            }

            $dados = [
                "status" => "Concluída",
                "data_conclusao" => date("Y-m-d H:i:s"),
                "observacao" => $_POST["observacao"] ?? null,
                "foto_depois" => $inspecaoAtual["foto_depois"]
            ];

            if (isset($_FILES["foto_depois"]) && $_FILES["foto_depois"]["error"] == 0) {
                if (!$this->s3Client) {
                    setFlashMessage("error", "Serviço de armazenamento não está configurado. Contacte o administrador.");
                    $this->showConcluirForm($id);
                    return;
                }

                $file = $_FILES["foto_depois"];
                $tempPath = $file['tmp_name'];
                $originalExt = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                $mimeType = mime_content_type($tempPath);

                try {
                    $image = Image::make($tempPath)
                        ->resize(1200, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });

                    $optimizedTempPath = tempnam(sys_get_temp_dir(), 'optimized_image_');
                    $fileName = 'fotos_depois/' . uniqid("inspecao_depois_", true);

                    if (in_array($originalExt, ['jpeg', 'jpg'])) {
                        $image->encode('jpg', 75)->save($optimizedTempPath);
                        $fileName .= ".jpg";
                        $contentType = 'image/jpeg';
                    } elseif ($originalExt === 'png') {
                        $image->encode('png', 90)->save($optimizedTempPath);
                        $fileName .= ".png";
                        $contentType = 'image/png';
                    } else {
                        $image->save($optimizedTempPath);
                        $fileName .= "." . $originalExt;
                        $contentType = $mimeType;
                    }

                    $result = $this->s3Client->putObject([
                        'Bucket' => getenv('S3_BUCKET'),
                        'Key' => $fileName,
                        'SourceFile' => $optimizedTempPath,
                        'ACL' => 'public-read',
                        'ContentType' => $contentType
                    ]);

                    $dados["foto_depois"] = $result['ObjectURL'];

                    if (!empty($inspecaoAtual['foto_depois'])) {
                        $oldKey = parse_url($inspecaoAtual['foto_depois'], PHP_URL_PATH);
                        if ($oldKey) {
                            $this->s3Client->deleteObject([
                                'Bucket' => getenv('S3_BUCKET'),
                                'Key' => ltrim($oldKey, '/')
                            ]);
                        }
                    }

                    unlink($optimizedTempPath);

                } catch (Exception $e) {
                    error_log("Erro ao otimizar ou fazer upload da foto de depois para o S3: " . $e->getMessage());
                    setFlashMessage("error", "Erro ao processar a foto de depois: " . $e->getMessage());
                    $this->showConcluirForm($id);
                    return;
                }
            }

            if ($this->inspecaoModel->atualizar($id, $dados)) {
                setFlashMessage("success", "Foto de depois e status atualizados com sucesso!");
            } else {
                setFlashMessage("error", "Erro ao atualizar inspeção com a nova foto.");
            }
            redirect("index.php?route=inspecoes&action=view&id=" . $id);
        }
    }

    public function gerarPdf($id)
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        // --- INÍCIO: CORREÇÃO PARA O DASHBOARD ---
        // Se a chamada veio sem ID, é a chamada do dashboard para exportar Excel.
        if (empty($id)) {
            $params = $_GET;
            unset($params['route']);
            unset($params['action']);
            $queryString = http_build_query($params);

            // Redireciona para a ação correta (downloadExcel)
            redirect("index.php?route=inspecoes&action=downloadExcel&" . $queryString);
            return;
        }
        // --- FIM: CORREÇÃO PARA O DASHBOARD ---

        $inspecao = $this->inspecaoModel->obterPorId($id);
        if (!$inspecao) {
            setFlashMessage("error", "Inspeção não encontrada.");
            redirect("index.php?route=inspecoes");
            return;
        }

        $usuarioEmpresaId = isset($_SESSION["user_empresa_id"]) ? $_SESSION["user_empresa_id"] : null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? '';

        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para gerar PDF desta inspeção.");
            redirect("index.php?route=inspecoes");
            return;
        }

        // Incluir a biblioteca FPDF
        require_once BASE_PATH . '/vendor/fpdf/fpdf.php';

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('Relatório de Inspeção'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Detalhes da Inspeção'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, utf8_decode('ID: ' . $inspecao['id']), 0, 1, 'L');
        $pdf->Cell(0, 7, utf8_decode('Data do Apontamento: ' . date('d/m/Y H:i:s', strtotime($inspecao['data_apontamento']))), 0, 1, 'L');
        $pdf->Cell(0, 7, utf8_decode('Setor: ' . $inspecao['setor_nome']), 0, 1, 'L');
        $pdf->Cell(0, 7, utf8_decode('Local: ' . $inspecao['local_nome']), 0, 1, 'L');
        $pdf->Cell(0, 7, utf8_decode('Tipo de Apontamento: ' . $inspecao['tipo_nome']), 0, 1, 'L');
        $pdf->MultiCell(0, 7, utf8_decode('Apontamento: ' . $inspecao['apontamento']), 0, 'L');
        $pdf->MultiCell(0, 7, utf8_decode('Risco/Consequência: ' . ($inspecao['risco_consequencia'] ?? 'N/A')), 0, 'L');
        $pdf->MultiCell(0, 7, utf8_decode('Resolução Proposta: ' . ($inspecao['resolucao_proposta'] ?? 'N/A')), 0, 'L');
        $pdf->Cell(0, 7, utf8_decode('Responsável: ' . $inspecao['responsavel']), 0, 1, 'L'); // <-- CORRIGIDO
        $pdf->Cell(0, 7, utf8_decode('Prazo: ' . ($inspecao['prazo'] ? date('d/m/Y', strtotime($inspecao['prazo'])) : 'N/A')), 0, 1, 'L');
        $pdf->Cell(0, 7, utf8_decode('Status: ' . $inspecao['status']), 0, 1, 'L');
        $pdf->Cell(0, 7, utf8_decode('Data de Conclusão: ' . ($inspecao['data_conclusao'] ? date('d/m/Y H:i:s', strtotime($inspecao['data_conclusao'])) : 'N/A')), 0, 1, 'L');
        $pdf->MultiCell(0, 7, utf8_decode('Observação: ' . ($inspecao['observacao'] ?? 'N/A')), 0, 'L');
        $pdf->Ln(10);

        // Fotos
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Fotos'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);

        if ($inspecao['foto_antes']) {
            $pdf->Cell(0, 7, utf8_decode('Foto Antes:'), 0, 1, 'L');
            // Baixar a imagem do S3/MinIO para um arquivo temporário
            $tempFotoAntesPath = tempnam(sys_get_temp_dir(), 'foto_antes_');
            try {
                $bucket = getenv('S3_BUCKET');
                $key = parse_url($inspecao['foto_antes'], PHP_URL_PATH);
                if ($key) {
                    $result = $this->s3Client->getObject([
                        'Bucket' => $bucket,
                        'Key' => ltrim($key, '/')
                    ]);
                    file_put_contents($tempFotoAntesPath, $result['Body']);
                    $pdf->Image($tempFotoAntesPath, $pdf->GetX(), $pdf->GetY(), 100);
                    $pdf->Ln(105); // Ajusta o espaçamento após a imagem
                }
            } catch (\Aws\S3\Exception\S3Exception $e) {
                error_log("Erro ao baixar foto_antes do S3 para PDF: " . $e->getMessage());
                $pdf->Cell(0, 7, utf8_decode('Erro ao carregar foto antes.'), 0, 1, 'L');
            } finally {
                if (file_exists($tempFotoAntesPath)) {
                    unlink($tempFotoAntesPath);
                }
            }
        }

        if (isset($inspecao['foto_depois']) && $inspecao['foto_depois']) {
            $pdf->Cell(0, 7, utf8_decode('Foto Depois:'), 0, 1, 'L');
            // Baixar a imagem do S3/MinIO para um arquivo temporário
            $tempFotoDepoisPath = tempnam(sys_get_temp_dir(), 'foto_depois_');
            try {
                $bucket = getenv('S3_BUCKET');
                $key = parse_url($inspecao['foto_depois'], PHP_URL_PATH);
                if ($key) {
                    $result = $this->s3Client->getObject([
                        'Bucket' => $bucket,
                        'Key' => ltrim($key, '/')
                    ]);
                    file_put_contents($tempFotoDepoisPath, $result['Body']);
                    $pdf->Image($tempFotoDepoisPath, $pdf->GetX(), $pdf->GetY(), 100);
                    $pdf->Ln(105); // Ajusta o espaçamento após a imagem
                }
            } catch (\Aws\S3\Exception\S3Exception $e) {
                error_log("Erro ao baixar foto_depois do S3 para PDF: " . $e->getMessage());
                $pdf->Cell(0, 7, utf8_decode('Erro ao carregar foto depois.'), 0, 1, 'L');
            } finally {
                if (file_exists($tempFotoDepoisPath)) {
                    unlink($tempFotoDepoisPath);
                }
            }
        }

        // Planos de Ação
        if (!empty($planos_acao)) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode('Planos de Ação'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            foreach ($planos_acao as $plano) {
                $pdf->MultiCell(0, 7, utf8_decode('Descrição: ' . $plano['descricao']), 0, 'L');
                $pdf->Cell(0, 7, utf8_decode('Prazo: ' . date('d/m/Y', strtotime($plano['prazo']))), 0, 1, 'L');
                $pdf->Cell(0, 7, utf8_decode('Responsável: ' . $plano['responsavel_nome']), 0, 1, 'L');
                $pdf->Cell(0, 7, utf8_decode('Status: ' . $plano['status']), 0, 1, 'L');
                $pdf->Ln(5);
            }
        }

        $pdf->Output('I', 'inspecao_' . $id . '.pdf');
        exit;
    }

    public function downloadExcel()
    {
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $filtros = [
            "empresa_id" => $_GET["empresa_id"] ?? null,
            "status" => $_GET["status"] ?? null,
            "usuario_nivel" => $_SESSION["user_nivel"] ?? '',
            "usuario_empresa_id" => $_SESSION["user_empresa_id"] ?? null,
        ];

        // Se não houver filtros, redireciona para a lista de inspeções
        if (empty($filtros['empresa_id']) || empty($filtros['status'])) {
            setFlashMessage("error", "Selecione a empresa e o status para gerar o relatório.");
            redirect("index.php?route=inspecoes");
            return;
        }

        // Busca todas as inspeções que atendem aos filtros (sem paginação)
        $inspecoes = $this->inspecaoModel->listar($filtros, 1, 999999);



        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Cabeçalho
        $header = [
            'Nº Apontamento',
            'Data Apontamento',
            'Setor',
            'Local',
            'Tipo',
            'Apontamento',
            'Risco/Consequência',
            'Resolução Proposta',
            'Responsável',
            'Prazo',
            'Status',
            'Data Conclusão',
            'Observação',
            'Empresa'
        ];
        $sheet->fromArray($header, NULL, 'A1');

        // Dados
        $row = 2;
        foreach ($inspecoes as $inspecao) {
            $data = [
                $inspecao['numero_inspecao'],
                date('d/m/Y H:i:s', strtotime($inspecao['data_apontamento'])),
                $inspecao['setor_nome'],
                $inspecao['local_nome'],
                $inspecao['tipo_nome'],
                $inspecao['apontamento'],
                $inspecao['risco_consequencia'],
                $inspecao['resolucao_proposta'],
                $inspecao['responsavel'], // <-- CORRIGIDO
                $inspecao['prazo'] ? date('d/m/Y', strtotime($inspecao['prazo'])) : '',
                $inspecao['status'],
                $inspecao['data_conclusao'] ? date('d/m/Y H:i:s', strtotime($inspecao['data_conclusao'])) : '',
                $inspecao['observacao'],
                $inspecao['empresa_nome']
            ];
            $sheet->fromArray($data, NULL, 'A' . $row++);
        }

        // Configurações de download
        $filename = 'relatorio_inspecoes_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
?>