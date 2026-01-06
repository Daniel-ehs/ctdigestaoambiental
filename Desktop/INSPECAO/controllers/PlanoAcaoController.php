<?php
/**
 * Controlador de Planos de Ação
 * VERSÃO ATUALIZADA COM UPLOAD PARA S3 (MINIO) E OTIMIZAÇÃO DE IMAGENS
 * VERSÃO COM MÉTODO PDF INTELIGENTE (BUSCA NO BANCO ANTES DE GERAR)
 */

// Inclui o autoload do Composer para carregar o AWS SDK e a Intervention Image
require_once BASE_PATH . '/vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Intervention\Image\ImageManagerStatic as Image;

class PlanoAcaoController {
    private $planoAcaoModel;
    private $inspecaoModel;
    private $empresaModel;
    private $s3Client; // Novo cliente S3
    
    public function __construct() {
        require_once 'models/PlanoAcao.php';
        require_once 'models/Inspecao.php';
        require_once 'models/Empresa.php';
        require_once 'services/PlanoPDFService.php'; // Serviço para planos de ação
        
        $this->planoAcaoModel = new PlanoAcao();
        $this->inspecaoModel = new Inspecao();
        $this->empresaModel = new Empresa();

        // --- INÍCIO: CONFIGURAÇÃO DO CLIENTE S3 (MINIO) ---
        // As credenciais são lidas das variáveis de ambiente que configurou no CapRover
        try {
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'region'  => getenv('S3_REGION'),
                'endpoint' => getenv('S3_ENDPOINT'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => getenv('S3_KEY'),
                    'secret' => getenv('S3_SECRET'),
                ],
            ]);
        } catch (Exception $e) {
            // Se falhar, regista o erro mas não impede a app de carregar
            error_log("Erro ao inicializar o cliente S3 no PlanoAcaoController: " . $e->getMessage());
            $this->s3Client = null;
        }
        // --- FIM: CONFIGURAÇÃO DO CLIENTE S3 (MINIO) ---

        // Configura o driver da Intervention Image para usar GD
        Image::configure(['driver' => 'gd']);
    }
    
    /**
     * Listar planos de ação
     */
    public function index() {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Obter lista de empresas para o filtro
        $empresas = $this->empresaModel->getAll();
        
        // Verificar permissões de acesso por empresa
        $empresaId = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;
        $usuarioEmpresaId = isset($_SESSION['user_empresa_id']) ? $_SESSION['user_empresa_id'] : null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId) {
            // Se não for admin e tiver empresa vinculada, só pode ver dados da sua empresa
            $empresaId = $usuarioEmpresaId;
        }
        
        // Obter filtros da requisição
        $filtros = [
            'setor_id' => $_GET['setor_id'] ?? null,
            'local_id' => $_GET['local_id'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'empresa_id' => $empresaId,
            'usuario_empresa_id' => $usuarioEmpresaId,
            'usuario_nivel' => $usuarioNivel
        ];
        
        // Obter página atual e itens por página
        $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $porPagina = 10; // Ou buscar de uma configuração
        
        // Obter planos de ação e total de registros
        $resultado = $this->planoAcaoModel->listar($filtros, $pagina, $porPagina);
        
        // Extrair dados para a view
        $planos = $resultado['planos'] ?? [];
        $totalPlanos = $resultado['total'] ?? 0;
        
        // Calcular total de páginas
        $totalPaginas = $porPagina > 0 ? ceil($totalPlanos / $porPagina) : 1;
        
        // Garantir que a página atual não exceda o total de páginas
        if ($pagina > $totalPaginas && $totalPaginas > 0) {
            $pagina = $totalPaginas; // Redireciona para a última página se a página solicitada for inválida
            // Opcional: Recarregar os dados para a página correta
            $resultado = $this->planoAcaoModel->listar($filtros, $pagina, $porPagina);
            $planos = $resultado['planos'] ?? [];
        }

        // --- LÓGICA ALTERADA ---
        // Carregar dados para filtros de forma condicional para a view
        require_once 'models/Setor.php';
        $setorModel = new Setor();
        // Carrega os setores baseados na empresa filtrada, se houver
        $setores = $setorModel->listar(true, $filtros['empresa_id']);

        require_once 'models/Local.php';
        $localModel = new Local();
        // Carrega os locais baseados no setor E empresa filtrados, se houver
        $locais = $localModel->listar(true, $filtros['setor_id'], $filtros['empresa_id']);
        // --- FIM DA ALTERAÇÃO ---
        
        // Exibir lista de planos de ação, passando as variáveis necessárias
        include 'views/planos_acao/index.php';
    }
    
    /**
     * Exibir formulário de criação de plano de ação
     * @param int $inspecaoId ID da inspeção
     */
    public function create($inspecaoId) {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Obter inspeção
        $inspecao = $this->inspecaoModel->obterPorId($inspecaoId);
        
        if (!$inspecao) {
            setFlashMessage('error', 'Inspeção não encontrada.');
            redirect('index.php?route=inspecoes');
        }
        
        // Verificar permissões de acesso por empresa
        $usuarioEmpresaId = isset($_SESSION['user_empresa_id']) ? $_SESSION['user_empresa_id'] : null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao['empresa_id'] != $usuarioEmpresaId) {
            setFlashMessage('error', 'Você não tem permissão para criar planos de ação para esta inspeção.');
            redirect('index.php?route=inspecoes');
            return;
        }
        
        // Verificar se já existe plano de ação para esta inspeção
        if ($this->planoAcaoModel->existePlanoParaInspecao($inspecaoId)) {
            setFlashMessage('error', 'Esta inspeção já possui um plano de ação.');
            redirect('index.php?route=inspecoes');
        }
        
        // Exibir formulário de criação
        include 'views/planos_acao/create.php';
    }
    
    /**
     * **MÉTODO ATUALIZADO PARA USAR S3 (MINIO) E OTIMIZAR IMAGENS**
     * Processar criação de plano de ação
     */
    public function store() {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=planos');
        }
        
        // Obter dados do formulário
        $inspecaoId = $_POST['inspecao_id'] ?? null;
        $descricaoAcao = $_POST['descricao_acao'] ?? null;
        
        // Validar campos obrigatórios
        if (empty($inspecaoId) || empty($descricaoAcao)) {
            setFlashMessage('error', 'Preencha todos os campos obrigatórios.');
            redirect("index.php?route=planos&action=create&inspecao_id={$inspecaoId}");
        }
        
        // Obter inspeção
        $inspecao = $this->inspecaoModel->obterPorId($inspecaoId);
        
        if (!$inspecao) {
            setFlashMessage('error', 'Inspeção não encontrada.');
            redirect('index.php?route=inspecoes');
        }
        
        // Verificar permissões de acesso por empresa
        $usuarioEmpresaId = isset($_SESSION['user_empresa_id']) ? $_SESSION['user_empresa_id'] : null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $inspecao['empresa_id'] != $usuarioEmpresaId) {
            setFlashMessage('error', 'Você não tem permissão para criar planos de ação para esta inspeção.');
            redirect('index.php?route=inspecoes');
            return;
        }
        
        // Verificar se já existe plano de ação para esta inspeção
        if ($this->planoAcaoModel->existePlanoParaInspecao($inspecaoId)) {
            setFlashMessage('error', 'Esta inspeção já possui um plano de ação.');
            redirect('index.php?route=inspecoes');
        }
        
        // Preparar dados do plano de ação
        $dados = [
            'inspecao_id' => $inspecaoId,
            'descricao_acao' => $descricaoAcao,
            'usuario_id' => $_SESSION['user_id'],
            'foto_depois' => null // Começa como nulo
        ];
        
        // --- INÍCIO: NOVA LÓGICA DE UPLOAD E OTIMIZAÇÃO PARA FOTO_DEPOIS ---
        if (isset($_FILES['foto_depois']) && $_FILES['foto_depois']['error'] === UPLOAD_ERR_OK) {
            if (!$this->s3Client) {
                setFlashMessage('error', 'Serviço de armazenamento não está configurado. Contacte o administrador.');
                redirect("index.php?route=planos&action=create&inspecao_id={$inspecaoId}");
                return;
            }

            $file = $_FILES['foto_depois'];
            $tempPath = $file['tmp_name'];
            $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mimeType = mime_content_type($tempPath);

            // Verificar extensão permitida
            if (!in_array($originalExt, ALLOWED_EXTENSIONS)) {
                setFlashMessage('error', 'Formato de arquivo não permitido. Use: ' . implode(', ', ALLOWED_EXTENSIONS));
                redirect("index.php?route=planos&action=create&inspecao_id={$inspecaoId}");
                return;
            }

            try {
                // Otimiza a imagem
                $image = Image::make($tempPath)
                    ->resize(1200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                $optimizedTempPath = tempnam(sys_get_temp_dir(), 'optimized_image_');
                $fileName = 'fotos_depois/' . uniqid("plano_depois_", true);

                // Decide o formato de saída e o Content-Type
                if (in_array($originalExt, ['jpeg', 'jpg'])) {
                    $image->encode('jpg', 75)->save($optimizedTempPath);
                    $fileName .= ".jpg";
                    $contentType = 'image/jpeg';
                } elseif ($originalExt === 'png') {
                    $image->encode('png', 90)->save($optimizedTempPath);
                    $fileName .= ".png";
                    $contentType = 'image/png';
                } elseif ($originalExt === 'webp') {
                    // Converter WebP para JPG (melhor compatibilidade)
                    $image->encode('jpg', 75)->save($optimizedTempPath);
                    $fileName .= ".jpg";
                    $contentType = 'image/jpeg';
                } elseif ($originalExt === 'gif') {
                    $image->encode('gif')->save($optimizedTempPath);
                    $fileName .= ".gif";
                    $contentType = 'image/gif';
                } else {
                    // Para outros formatos, converte para JPG como padrão
                    $image->encode('jpg', 75)->save($optimizedTempPath);
                    $fileName .= ".jpg";
                    $contentType = 'image/jpeg';
                }

                // Faz o upload da imagem otimizada para o S3
                $result = $this->s3Client->putObject([
                    'Bucket' => getenv('S3_BUCKET'),
                    'Key'    => $fileName,
                    'SourceFile' => $optimizedTempPath,
                    'ACL'    => 'public-read',
                    'ContentType' => $contentType
                ]);
                
                // Guarda o URL completo do objeto no banco de dados
                $dados['foto_depois'] = $result['ObjectURL'];

                // Remove o arquivo temporário otimizado
                unlink($optimizedTempPath);

            } catch (Exception $e) {
                error_log("Erro ao otimizar ou fazer upload da foto_depois para o S3: " . $e->getMessage());
                setFlashMessage('error', 'Erro ao processar a foto: ' . $e->getMessage());
                redirect("index.php?route=planos&action=create&inspecao_id={$inspecaoId}");
                return;
            }
        }
        // --- FIM: NOVA LÓGICA DE UPLOAD E OTIMIZAÇÃO ---
        
        // Criar plano de ação
        $planoId = $this->planoAcaoModel->criar($dados);
        
        if ($planoId) {
            setFlashMessage('success', 'Plano de ação registrado com sucesso.');
            redirect("index.php?route=planos&action=view&id={$planoId}");
        } else {
            setFlashMessage('error', 'Erro ao registrar plano de ação.');
            redirect("index.php?route=planos&action=create&inspecao_id={$inspecaoId}");
        }
    }
    
    /**
     * **MÉTODO PDF ATUALIZADO - VERSÃO INTELIGENTE**
     * Gerar ou buscar PDF do plano de ação
     * 
     * Este método:
     * 1. Verifica se já existe um PDF salvo no banco de dados
     * 2. Se existir: Redireciona para o PDF do MinIO (rápido!)
     * 3. Se não existir: Gera um novo PDF e salva no MinIO
     * 
     * @param int $id ID do plano de ação
     */
    public function pdf($id) {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Buscar dados do plano
        $plano = $this->planoAcaoModel->obterCompletoPorId($id);
        
        if (!$plano) {
            setFlashMessage('error', 'Plano de Ação não encontrado.');
            redirect('index.php?route=planos');
            return;
        }
        
        // Verificar permissão
        $usuarioEmpresaId = isset($_SESSION['user_empresa_id']) ? $_SESSION['user_empresa_id'] : null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $plano['empresa_id'] != $usuarioEmpresaId) {
            setFlashMessage('error', 'Você não tem permissão para acessar este plano de ação.');
            redirect('index.php?route=planos');
            return;
        }
        
        // --- INÍCIO: VERIFICAR SE JÁ EXISTE PDF NO BANCO ---
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar o relatório mais recente deste plano de ação
            $stmt = $db->prepare("
                SELECT url_minio, nome_arquivo, data_geracao 
                FROM relatorios 
                WHERE tipo_relatorio = 'plano_acao' 
                AND referencia_id = ? 
                ORDER BY data_geracao DESC 
                LIMIT 1
            ");
            $stmt->execute([$id]);
            $relatorio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Se encontrou um PDF salvo, redirecionar para ele
            if ($relatorio && !empty($relatorio['url_minio'])) {
                error_log("PlanoAcaoController->pdf: PDF encontrado no banco, redirecionando para: " . $relatorio['url_minio']);
                
                // Redirecionar diretamente para o PDF do MinIO
                header('Location: ' . $relatorio['url_minio']);
                exit;
            }
            
            error_log("PlanoAcaoController->pdf: Nenhum PDF encontrado no banco, gerando novo...");
            
        } catch (Exception $e) {
            error_log("PlanoAcaoController->pdf: Erro ao buscar PDF no banco: " . $e->getMessage());
            // Continua para gerar um novo PDF
        }
        // --- FIM DA VERIFICAÇÃO ---
        
        // Se não encontrou PDF salvo, gerar um novo
        if (!class_exists('PlanoPDFService')) {
            setFlashMessage('error', 'Serviço de geração de PDF para planos de ação não encontrado.');
            redirect('index.php?route=planos');
        }
        $pdfService = new PlanoPDFService();
        
        // Gerar PDF
        try {
            $pdfPath = $pdfService->gerarPlanoPDF($plano);
            
            if ($pdfPath && file_exists($pdfPath)) {
                // Após gerar, buscar novamente o URL do MinIO
                $stmt = $db->prepare("
                    SELECT url_minio 
                    FROM relatorios 
                    WHERE tipo_relatorio = 'plano_acao' 
                    AND referencia_id = ? 
                    ORDER BY data_geracao DESC 
                    LIMIT 1
                ");
                $stmt->execute([$id]);
                $novoRelatorio = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Se foi salvo no MinIO, redirecionar para lá
                if ($novoRelatorio && !empty($novoRelatorio['url_minio'])) {
                    error_log("PlanoAcaoController->pdf: PDF gerado e salvo, redirecionando para: " . $novoRelatorio['url_minio']);
                    header('Location: ' . $novoRelatorio['url_minio']);
                    exit;
                }
                
                // Se não foi salvo no MinIO, servir o arquivo local
                error_log("PlanoAcaoController->pdf: PDF gerado mas não salvo no MinIO, servindo arquivo local");
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="plano_acao_' . $id . '.pdf"');
                header('Content-Length: ' . filesize($pdfPath));
                readfile($pdfPath);
                exit;
            } else {
                setFlashMessage('error', 'Erro ao gerar ou localizar o PDF do plano de ação.');
                redirect('index.php?route=planos');
            }
        } catch (Exception $e) {
            error_log("PlanoAcaoController->pdf: Erro ao gerar PDF: " . $e->getMessage());
            setFlashMessage('error', 'Ocorreu um erro inesperado ao gerar o PDF.');
            redirect('index.php?route=planos');
        }
    }
    
    /**
     * Gerar PDF do plano de ação (método antigo mantido para compatibilidade)
     * @param int $id ID do plano de ação
     */
    public function generatePDF($id) {
        // Redireciona para o novo método pdf()
        $this->pdf($id);
    }
    
    /**
     * Exibir detalhes do plano de ação
     * @param int $id ID do plano de ação
     */
    public function view($id) {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }

        // Agora, esta função busca todos os dados do plano e da inspeção de uma só vez
        $plano = $this->planoAcaoModel->obterCompletoPorId($id);

        if (!$plano) {
            setFlashMessage('error', 'Plano de Ação não encontrado.');
            redirect('index.php?route=planos');
            return;
        }

        // Verifica permissão de acesso
        $usuarioEmpresaId = $_SESSION['user_empresa_id'] ?? null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';

        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && isset($plano['empresa_id']) && $plano['empresa_id'] != $usuarioEmpresaId) {
            setFlashMessage('error', 'Você não tem permissão para visualizar este plano de ação.');
            redirect('index.php?route=planos');
            return;
        }

        include 'views/planos_acao/view.php';
    }

    /**
     * Excluir um plano de ação.
     * @param int $id O ID do plano de ação a ser excluído.
     */
    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }

        // Obtém os detalhes do plano para verificar a permissão antes de excluir
        $plano = $this->planoAcaoModel->obterPorId($id);

        if (!$plano) {
            setFlashMessage('error', 'Plano de Ação não encontrado.');
            redirect('index.php?route=planos');
            return;
        }

        // Verifica a permissão de acesso
        $usuarioEmpresaId = $_SESSION['user_empresa_id'] ?? null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';

        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && isset($plano['empresa_id']) && $plano['empresa_id'] != $usuarioEmpresaId) {
            setFlashMessage('error', 'Você não tem permissão para excluir este plano de ação.');
            redirect('index.php?route=planos');
            return;
        }

        // Tenta excluir o plano usando o método 'excluir' do modelo
        if ($this->planoAcaoModel->excluir($id)) {
            setFlashMessage('success', 'Plano de ação excluído com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao excluir o plano de ação.');
        }

        redirect('index.php?route=planos');
    }
}
