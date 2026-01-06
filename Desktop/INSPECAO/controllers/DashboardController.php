<?php
/**
 * Controlador de Dashboard
 */
class DashboardController {
    private $inspecaoModel;
    private $setorModel;
    private $tipoApontamentoModel;
    private $projetoModel;
    private $empresaModel;
    
    public function __construct() {
        // O construtor não fará a instanciação, pois precisamos da conexão $pdo primeiro.
        // A lógica foi movida para o método index().
    }
    
    /**
     * Exibir dashboard
     */
    public function index() {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }

        // --- INÍCIO DA CORREÇÃO FINAL ---

        // 1. Inclui todos os arquivos de Model e a classe de conexão
        require_once 'config/database.php';
        require_once 'models/Inspecao.php';
        require_once 'models/Setor.php';
        require_once 'models/TipoApontamento.php';
        require_once 'models/Projeto.php';
        require_once 'models/Empresa.php';
        
        // 2. Obtém a conexão com o banco de dados UMA VEZ
        $pdo = Database::getInstance()->getConnection();

        // 3. Instancia os models, respeitando a necessidade de cada um
        
        // Models que PRECISAM da conexão ($pdo) como argumento
        $this->inspecaoModel = new Inspecao($pdo);
        $this->projetoModel = new Projeto($pdo); // Corrigido: Agora recebe a conexão
        $this->empresaModel = new Empresa($pdo);

        // Models que NÃO precisam da conexão (conforme código que você enviou)
        $this->setorModel = new Setor();
        $this->tipoApontamentoModel = new TipoApontamento();
        
        // --- FIM DA CORREÇÃO FINAL ---

        // Lógica do filtro (já corrigida para tratar "Todas as Empresas")
        $empresaId = !empty($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;
        
        // Obter lista de empresas para o filtro
        $empresas = $this->empresaModel->getAll();
        
        // Verificar permissões de acesso por empresa
        if ($_SESSION['user_nivel'] !== 'admin' && isset($_SESSION['user_empresa_id'])) {
            $empresaId = $_SESSION['user_empresa_id'];
        }
        
        // Obter dados para a view
        $estatisticasInspecoes = $this->inspecaoModel->obterEstatisticas($empresaId);
        $inspecoesPorSetor = $this->setorModel->contarInspecoes($empresaId);
        $inspecoesPorTipo = $this->tipoApontamentoModel->contarInspecoes($empresaId);
        $estatisticasProjetos = $this->projetoModel->obterEstatisticas($empresaId);
        $proximosVencimentos = $this->inspecaoModel->obterProximosVencimentos($empresaId, 7);

        // Extrair estatísticas para variáveis individuais
        $totalInspecoes = $estatisticasInspecoes['total'] ?? 0;
        $emAberto = $estatisticasInspecoes['em_aberto'] ?? 0;
        $concluidas = $estatisticasInspecoes['concluidas'] ?? 0;
        $prazoVencido = $estatisticasInspecoes['vencidas'] ?? 0;
        
        // Exibir a view do dashboard
        include 'views/dashboard/index.php';
    }
}