<?php
require_once 'models/ApontamentoPendente.php';
require_once 'models/Empresa.php';
require_once 'models/Setor.php';
require_once 'models/Local.php';
require_once 'models/Tipo.php';
require_once 'models/Inspecao.php';

class AprovacaoController {
    public function index() {
        // Verifica se o usuário está logado e tem nível de acesso adequado
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso não autorizado.');
            redirect('index.php?route=login');
            exit;
        }

        $apontamentoPendenteModel = new ApontamentoPendente();
        $apontamentos = $apontamentoPendenteModel->getPendingApontamentos();

        $empresaModel = new Empresa();
        $setorModel = new Setor();
        $localModel = new Local();

        $empresas = $empresaModel->getAll();
        $setores = $setorModel->listar();
        $locais = $localModel->listar();

        require_once BASE_PATH . '/views/admin/aprovacao.php';
    }

    public function aprovar($id) {
        // Verifica se o usuário está logado e tem nível de acesso adequado
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso não autorizado.');
            redirect('index.php?route=login');
            exit;
        }

        $apontamentoPendenteModel = new ApontamentoPendente();
        $inspecaoModel = new Inspecao();

        $apontamento = $apontamentoPendenteModel->getById($id);

        if (!$apontamento) {
            setFlashMessage('error', 'Apontamento pendente não encontrado.');
            redirect('index.php?route=aprovacao');
            return;
        }

        // Opção 1: Pré-preencher o formulário de inspeção
        // Redireciona para a página de criação de inspeção com os dados pré-preenchidos
        $query = http_build_query([
            'empresa_id' => $apontamento['empresa_id'],
            'setor_id' => $apontamento['setor_id'],
            'local_id' => $apontamento['local_id'],
            'apontamento' => $apontamento['apontamento'],
            'foto_antes' => $apontamento['foto_apontamento'],
            'apontamento_pendente_id' => $apontamento['id'] // Passa o ID do apontamento pendente
        ]);
        redirect('index.php?route=inspecoes&action=create&' . $query);
    }

    public function rejeitar($id) {
        // Verifica se o usuário está logado e tem nível de acesso adequado
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso não autorizado.');
            redirect('index.php?route=login');
            exit;
        }

        $apontamentoPendenteModel = new ApontamentoPendente();
        $usuarioValidacaoId = $_SESSION['user_id'];

        if ($apontamentoPendenteModel->updateStatus($id, 'rejeitado', $usuarioValidacaoId)) {
            setFlashMessage('success', 'Apontamento rejeitado com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao rejeitar apontamento.');
        }
        redirect('index.php?route=aprovacao');
    }
}


