<?php
// Salve este código como: controllers/PublicoController.php

// 1. Incluir todos os models necessários
require_once 'models/Empresa.php';
require_once 'models/Setor.php';
require_once 'models/Local.php';
require_once 'models/ApontamentoPendente.php';
require_once 'models/Inspecao.php';
require_once 'models/Projeto.php';
require_once 'models/TipoApontamento.php'; // <-- Model que estava faltando

class PublicoController {

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Instancia todos os models necessários
        $empresaModel = new Empresa();
        $setorModel = new Setor($db); // Assumindo que precisa de $db
        $inspecaoModel = new Inspecao($db); // Assumindo que precisa de $db
        $projetoModel = new Projeto($db);
        $tipoApontamentoModel = new TipoApontamento(); // <-- Instancia o model que faltava

        // --- FILTROS (Lógica mantida, está correta) ---
        $empresaId = filter_input(INPUT_GET, 'empresa_id', FILTER_VALIDATE_INT) ?: null;
        $ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: date('Y');
        $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: date('n');
        
        // --- BUSCA DE DADOS PARA A PLACA (LÓGICA CORRIGIDA) ---

        // CORREÇÃO 1: Buscar os IDs dos tipos de risco dinamicamente, igual ao PainelController
        $tiposRiscoIds = $tipoApontamentoModel->getIdsPorNomes(['Risco Potencial', 'Falta de Uso de EPI']);

        // Contar riscos eliminados no mês (agora com os IDs corretos)
        $riscosEliminadosMes = 0;
        if (!empty($tiposRiscoIds)) {
            $riscosEliminadosMes = $inspecaoModel->contarRiscosEliminadosMes($ano, $mes, $tiposRiscoIds, $empresaId);
        }

        // Contar riscos eliminados no ano (agora com os IDs corretos)
        $riscosEliminadosAno = 0;
        if (!empty($tiposRiscoIds)) {
            $riscosEliminadosAno = $inspecaoModel->contarRiscosEliminadosAnoAcumulado($ano, $mes, $tiposRiscoIds, $empresaId);
        }
        
        // CORREÇÃO 2: Usar o mesmo método do PainelController para contar projetos
        $projetosEmAndamento = $projetoModel->contarProjetosEmAndamento($ano, $mes, $empresaId);

        // --- DADOS PARA OS FORMULÁRIOS (Lógica mantida) ---
        $empresas = $empresaModel->getAll();
        $setores = $setorModel->listar(); 
        $anosDisponiveis = $inspecaoModel->getAnosDisponiveis();
        if (empty($anosDisponiveis)) {
            $anosDisponiveis[] = date('Y');
        }

        // Carrega a view, agora com os dados corretos e consistentes
        require_once 'views/publico/reportar.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=reportar');
        }

        $empresa_id = $_POST['empresa_id'] ?? null;
        $setor_id = $_POST['setor_id'] ?? null;
        $local_id = $_POST['local_id'] ?? null;
        $apontamento = $_POST['apontamento'] ?? null;
        $contato_nome = $_POST['contato_nome'] ?? null;
        $contato_info = $_POST['contato_info'] ?? null;

        if (empty($empresa_id) || empty($setor_id) || empty($local_id) || empty($apontamento)) {
            setFlashMessage('error', 'Por favor, preencha todos os campos obrigatórios.');
            $queryParams = http_build_query(array_filter(['empresa_id' => $empresa_id]));
            redirect('index.php?route=reportar&' . $queryParams);
            return;
        }

        $dados = [
            'empresa_id' => $empresa_id,
            'setor_id' => $setor_id,
            'local_id' => $local_id,
            'apontamento' => $apontamento,
            'contato_nome' => !empty($contato_nome) ? $contato_nome : null,
            'contato_info' => !empty($contato_info) ? $contato_info : null,
            'foto_apontamento' => null
        ];

        if (isset($_FILES['foto_apontamento']) && $_FILES['foto_apontamento']['error'] == 0) {
            $foto = $_FILES['foto_apontamento'];
            $uploadDir = UPLOADS_DIR . '/fotos_pendentes/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $fileName = 'apontamento_pendente_' . uniqid() . '_' . basename($foto['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($foto['tmp_name'], $uploadFile)) {
                $dados['foto_apontamento'] = $fileName;
            } else {
                setFlashMessage('error', 'Falha ao fazer upload da imagem.');
                redirect('index.php?route=reportar');
                return;
            }
        }

        $apontamentoPendenteModel = new ApontamentoPendente();
        if ($apontamentoPendenteModel->create($dados)) {
            $_SESSION['show_success_modal'] = true;
        } else {
            setFlashMessage('error', 'Ocorreu um erro ao registrar seu apontamento. Tente novamente.');
        }
        
        $redirectParams = http_build_query(array_filter([
            'empresa_id' => filter_input(INPUT_GET, 'empresa_id'),
            'ano' => filter_input(INPUT_GET, 'ano'),
            'mes' => filter_input(INPUT_GET, 'mes')
        ]));

        redirect('index.php?route=reportar&' . $redirectParams);
    }
}