<?php
/**
 * Controlador do Painel de Segurança (Placa)
 */
class PainelController {
    private $inspecaoModel;
    private $projetoModel;
    private $empresaModel;
    private $tipoApontamentoModel;

    public function __construct() {
        // Carrega os models necessários
        require_once 'models/Inspecao.php';
        require_once 'models/Projeto.php';
        require_once 'models/Empresa.php';
        require_once 'models/TipoApontamento.php';

        // Instancia os models
        $pdo = Database::getInstance()->getConnection();
        $this->inspecaoModel = new Inspecao($pdo);
        $this->projetoModel = new Projeto($pdo);
        $this->empresaModel = new Empresa(); // Este model não precisa de $pdo, conforme seu código
        $this->tipoApontamentoModel = new TipoApontamento(); // Este também não
    }

    /**
     * Exibe a página da Placa de Segurança
     */
    public function placa() {
        // --- FILTROS ---
        // Pega os valores do formulário ou define valores padrão (mês/ano atual)
        $empresaId = !empty($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;
        $ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');
        $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');

        // --- BUSCA DE DADOS ---
        // 1. Obter os IDs dos tipos de apontamento "Risco Potencial" e "Falta de Uso de EPI"
        $tiposRiscoIds = $this->tipoApontamentoModel->getIdsPorNomes(['Risco Potencial', 'Falta de Uso de EPI']);

        // 2. Contar riscos eliminados no mês selecionado
        $riscosEliminadosMes = 0;
        if (!empty($tiposRiscoIds)) {
            $riscosEliminadosMes = $this->inspecaoModel->contarRiscosEliminadosMes($ano, $mes, $tiposRiscoIds, $empresaId);
        }

        // 3. Contar riscos eliminados no ano (acumulado até o mês selecionado)
        $riscosEliminadosAno = 0;
        if (!empty($tiposRiscoIds)) {
            $riscosEliminadosAno = $this->inspecaoModel->contarRiscosEliminadosAnoAcumulado($ano, $mes, $tiposRiscoIds, $empresaId);
        }
        
        // 4. Contar projetos em andamento
        $projetosEmAndamento = $this->projetoModel->contarProjetosEmAndamento($ano, $mes, $empresaId);

        // --- DADOS PARA OS FILTROS DO FORMULÁRIO ---
        $empresas = $this->empresaModel->getAll();
        $anosDisponiveis = $this->inspecaoModel->getAnosDisponiveis();

        // Carrega a view e passa todas as variáveis
        include 'views/painel/placa.php';
    }
}