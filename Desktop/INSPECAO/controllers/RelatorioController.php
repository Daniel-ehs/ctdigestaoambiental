<?php
/**
 * Controlador de Relatórios
 */

require_once BASE_PATH . '/vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class RelatorioController
{
    private $inspecaoModel;
    private $setorModel;
    private $localModel;
    private $empresaModel;
    private $s3Client;

    public function __construct()
    {
        try {
            require_once 'models/Inspecao.php';
            require_once 'models/Setor.php';
            require_once 'models/Local.php';
            require_once 'models/Empresa.php';

            $this->inspecaoModel = new Inspecao();
            $this->setorModel = new Setor();
            $this->localModel = new Local();
            $this->empresaModel = new Empresa();

            // --- INÍCIO: CONFIGURAÇÃO DO CLIENTE S3 (MINIO) ---
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
                error_log("Erro ao inicializar o cliente S3 no RelatorioController: " . $e->getMessage());
                $this->s3Client = null;
            }
            // --- FIM: CONFIGURAÇÃO DO CLIENTE S3 (MINIO) ---

        } catch (Throwable $e) {
            error_log("Erro Fatal no construtor do RelatorioController: " . $e->getMessage());
            throw $e;
        }
    }

    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    private function getRelatoriosEmitidos()
    {
        $jsonPath = 'uploads/pdfs/reports.json';
        if (!file_exists($jsonPath)) {
            return [];
        }
        $relatorios = json_decode(file_get_contents($jsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        $relatoriosExistentes = array_filter($relatorios, function ($report) {
            // Verificar se o arquivo existe localmente ou no S3
            if (isset($report['file'])) {
                if (strpos($report['file'], 'http') === 0) { // É um URL S3
                    // Não podemos verificar a existência de um URL S3 diretamente aqui sem uma requisição HTTP
                    // Por simplicidade, assumimos que se é um URL, ele existe. Em um ambiente real, você pode querer adicionar uma verificação de headObject.
                    return true;
                } else { // É um arquivo local
                    return file_exists('uploads/pdfs/' . $report['file']);
                }
            }
            return false;
        });
        usort($relatoriosExistentes, function ($a, $b) {
            return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
        });
        return $relatoriosExistentes;
    }

    private function saveRelatoriosEmitidos($relatorios)
    {
        $jsonPath = 'uploads/pdfs/reports.json';
        $relatorios = array_values($relatorios);
        file_put_contents($jsonPath, json_encode($relatorios, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        $empresas = $this->empresaModel->getAll();
        // --- ALTERAÇÃO: Removido loop de semanas calculado no servidor ---
        // Agora passamos os anos disponíveis para o select
        $anosDisponiveis = $this->inspecaoModel->getAnosDisponiveis();
        // Garante que o ano atual e próximo estejam na lista
        $anoAtualStr = date('Y');
        $proximoAnoStr = date('Y', strtotime('+1 year'));
        if (!in_array($anoAtualStr, $anosDisponiveis))
            $anosDisponiveis[] = $anoAtualStr;
        if (!in_array($proximoAnoStr, $anosDisponiveis))
            $anosDisponiveis[] = $proximoAnoStr;
        rsort($anosDisponiveis); // Ordena decrescente (mais recente primeiro)
        $allRelatorios = $this->getRelatoriosEmitidos();

        // --- Lógica de Paginação e Busca ---
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Filtragem por busca
        if (!empty($search)) {
            $allRelatorios = array_filter($allRelatorios, function ($report) use ($search) {
                $term = strtolower($search);
                return strpos(strtolower($report['file']), $term) !== false ||
                    strpos(strtolower($report['empresa_nome']), $term) !== false;
            });
        }

        $totalRelatorios = count($allRelatorios);
        $totalPages = ceil($totalRelatorios / $perPage);
        $offset = ($page - 1) * $perPage;

        // Fatiar o array para a página atual
        $relatoriosEmitidos = array_slice($allRelatorios, $offset, $perPage);

        $pagination = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalRelatorios,
            'searchQuery' => $search
        ];
        // -----------------------------------

        $route = 'relatorios';
        include 'views/relatorios/index.php';
    }

    public function semanal()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        $empresas = $this->empresaModel->getAll();
        $semanas = [];
        $dataAtual = new DateTime();
        for ($i = 0; $i < 12; $i++) {
            $data = clone $dataAtual;
            $data->modify("-{$i} weeks");
            $semanas[] = [
                'numero' => $data->format('W'),
                'ano' => $data->format('o'),
                'inicio' => (clone $data->modify('monday this week'))->format('d/m/Y'),
                'fim' => (clone $data->modify('sunday this week'))->format('d/m/Y')
            ];
        }
        $relatoriosEmitidos = $this->getRelatoriosEmitidos();
        $route = 'relatorios';
        include 'views/relatorios/semanal.php';
    }

    public function generate($type)
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=relatorios');
        }
        if ($type === 'semanal') {
            return $this->generateSemanal();
        }
        setFlashMessage('error', 'Tipo de relatório inválido.');
        redirect('index.php?route=relatorios');
    }

    private function generateSemanal()
    {
        $semana = $_POST['semana'] ?? null;
        $ano = $_POST['ano'] ?? null;
        $empresaId = $_POST['empresa_id'] ?? null;

        if (empty($semana) || empty($ano)) {
            $msg = 'Por favor, selecione o ano e a semana.';
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            setFlashMessage('error', $msg);
            redirect('index.php?route=relatorios');
            return;
        }

        // list($ano, $semana) = explode('-', $semanaEAno); // Removido explode antigo

        $usuarioEmpresaId = $_SESSION['user_empresa_id'] ?? null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId) {
            $empresaId = $usuarioEmpresaId;
        }

        $filtros = [
            'semana' => $semana,
            'ano' => $ano
        ];

        if (!empty($empresaId)) {
            $filtros['empresa_id'] = $empresaId;
        }

        $inspecoes = $this->inspecaoModel->listar($filtros);

        if (empty($inspecoes)) {
            $msg = 'Não foram encontradas inspeções para os filtros selecionados.';
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            setFlashMessage('error', $msg);
            redirect('index.php?route=relatorios');
            return;
        }

        require_once 'services/PDFService.php';
        $pdfService = new PDFService();

        try {
            $pdfContent = $pdfService->gerarRelatorioSemanal($inspecoes, $semana, $ano, true, true); // Retorna o conteúdo do PDF
        } catch (Exception $e) {
            $msg = 'Erro na geração do PDF: ' . $e->getMessage();
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            setFlashMessage('error', $msg);
            redirect('index.php?route=relatorios');
            return;
        }

        if ($pdfContent) {
            if (!$this->s3Client) {
                $msg = "Serviço de armazenamento não está configurado. Contacte o administrador.";
                if ($this->isAjax()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                }
                setFlashMessage("error", $msg);
                redirect("index.php?route=relatorios");
                return;
            }

            $fileName = 'relatorio_semanal_' . $ano . '_' . $semana . '_' . uniqid() . '.pdf';
            $bucket = getenv('S3_BUCKET_RELATORIOS');

            try {
                $result = $this->s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $fileName,
                    'Body' => $pdfContent,
                    'ACL' => 'public-read',
                    'ContentType' => 'application/pdf'
                ]);

                $pdfUrl = $result['ObjectURL'];

                $empresaNome = 'Todas as empresas';
                if (!empty($empresaId)) {
                    $empresas = $this->empresaModel->getAll();
                    foreach ($empresas as $empresa) {
                        if ($empresa['id'] == $empresaId) {
                            $empresaNome = $empresa['nome'];
                            break;
                        }
                    }
                }
                $relatorios = $this->getRelatoriosEmitidos();
                $novoRelatorio = [
                    'file' => $pdfUrl, // Salva o URL do S3
                    'empresa_nome' => $empresaNome,
                    'timestamp' => time(),
                    'semana' => $semana,
                    'ano' => $ano
                ];
                array_unshift($relatorios, $novoRelatorio);
                $this->saveRelatoriosEmitidos($relatorios);

                // Não redirecionar aqui, o redirecionamento será feito via JavaScript na view
                // setFlashMessage("success", "Relatório semanal gerado e salvo no MinIO com sucesso!");
                // redirect("index.php?route=relatorios");
                // return;
                echo json_encode(['success' => true, 'pdfUrl' => $pdfUrl, 'message' => 'Relatório gerado e salvo com sucesso!']);
                exit;

            } catch (S3Exception $e) {
                error_log("Erro ao fazer upload do PDF para o S3: " . $e->getMessage());
                $msg = 'Erro ao salvar o relatório no armazenamento: ' . $e->getMessage();
                if ($this->isAjax()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                }
                setFlashMessage('error', $msg);
                redirect('index.php?route=relatorios');
                return;
            }
        } else {
            $msg = 'Erro ao gerar o conteúdo do PDF do relatório.';
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            setFlashMessage('error', $msg);
            redirect('index.php?route=relatorios');
        }
    }

    public function deleteRelatorio()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=relatorios');
            return;
        }

        $fileToDelete = $_GET['file'] ?? null;
        if (empty($fileToDelete)) {
            setFlashMessage('error', 'Nome do arquivo não especificado.');
            redirect('index.php?route=relatorios');
            return;
        }

        // Se for um URL S3, extrair a chave
        if (strpos($fileToDelete, 'http') === 0) {
            $keyToDelete = parse_url($fileToDelete, PHP_URL_PATH);
            $keyToDelete = ltrim($keyToDelete, '/');
            $bucket = getenv('S3_BUCKET_RELATORIOS');

            if (!$this->s3Client) {
                setFlashMessage("error", "Serviço de armazenamento não está configurado. Contacte o administrador.");
                redirect('index.php?route=relatorios');
                return;
            }

            try {
                $this->s3Client->deleteObject([
                    'Bucket' => $bucket,
                    'Key' => $keyToDelete
                ]);
                $s3DeleteSuccess = true;
            } catch (S3Exception $e) {
                error_log("Erro ao excluir relatório do S3: " . $e->getMessage());
                setFlashMessage('error', 'Erro ao excluir o relatório do armazenamento: ' . $e->getMessage());
                redirect('index.php?route=relatorios');
                return;
            }
        } else { // Caso seja um arquivo local (legado)
            $filePath = 'uploads/pdfs/' . basename($fileToDelete);
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    setFlashMessage('error', 'Falha ao excluir o arquivo físico. Verifique as permissões do diretório.');
                    redirect('index.php?route=relatorios');
                    return;
                }
            }
        }

        $relatorios = $this->getRelatoriosEmitidos();
        $relatoriosFiltrados = [];
        $encontrado = false;

        foreach ($relatorios as $report) {
            if (isset($report['file']) && $report['file'] === $fileToDelete) {
                $encontrado = true;
            } else {
                $relatoriosFiltrados[] = $report;
            }
        }

        if ($encontrado) {
            $this->saveRelatoriosEmitidos($relatoriosFiltrados);
            setFlashMessage('success', 'Relatório excluído com sucesso.');
        } else {
            setFlashMessage('error', 'Relatório não encontrado no registro.');
        }

        redirect('index.php?route=relatorios');
    }
}

