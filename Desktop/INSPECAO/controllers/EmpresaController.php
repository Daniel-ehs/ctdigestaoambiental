<?php
/**
 * Controlador de Empresas
 */

class EmpresaController {
    private $empresaModel;
    
    public function __construct() {
        require_once BASE_PATH . '/models/Empresa.php';
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Exibir lista de empresas
     */
    public function index() {
        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se o usuário tem permissão de administrador
        if ($_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            redirect('index.php?route=dashboard');
        }
        
        // Obter lista de empresas
        $empresas = $this->empresaModel->getAll();
        
        // Incluir a view
        include BASE_PATH . '/views/empresas/index.php';
    }
    
    /**
     * Exibir formulário de criação de empresa
     */
    public function create() {
        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se o usuário tem permissão de administrador
        if ($_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            redirect('index.php?route=dashboard');
        }
        
        // Incluir a view
        include BASE_PATH . '/views/empresas/create.php';
    }
    
    /**
     * Processar formulário de criação de empresa
     */
    public function store() {
        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se o usuário tem permissão de administrador
        if ($_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            redirect('index.php?route=dashboard');
        }
        
        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=empresas');
        }
        
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        
        if (empty($nome)) {
            setFlashMessage('error', 'O nome da empresa é obrigatório.');
            redirect('index.php?route=empresas&action=create');
        }
        
        // Verificar se o nome já existe
        if ($this->empresaModel->nomeExiste($nome)) {
            setFlashMessage('error', 'Já existe uma empresa com este nome.');
            redirect('index.php?route=empresas&action=create');
        }
        
        // Criar empresa
        $dados = [
            'nome' => $nome
        ];
        
        $resultado = $this->empresaModel->create($dados);
        
        if ($resultado) {
            setFlashMessage('success', 'Empresa criada com sucesso.');
            redirect('index.php?route=empresas');
        } else {
            setFlashMessage('error', 'Erro ao criar empresa.');
            redirect('index.php?route=empresas&action=create');
        }
    }
    
    /**
     * Exibir formulário de edição de empresa
     * 
     * @param int $id ID da empresa
     */
    public function edit($id) {
        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se o usuário tem permissão de administrador
        if ($_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            redirect('index.php?route=dashboard');
        }
        
        // Obter dados da empresa
        $empresa = $this->empresaModel->getById($id);
        
        if (!$empresa) {
            setFlashMessage('error', 'Empresa não encontrada.');
            redirect('index.php?route=empresas');
        }
        
        // Incluir a view
        include BASE_PATH . '/views/empresas/edit.php';
    }
    
    /**
     * Processar formulário de edição de empresa
     * 
     * @param int $id ID da empresa
     */
    public function update($id) {
        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se o usuário tem permissão de administrador
        if ($_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            redirect('index.php?route=dashboard');
        }
        
        // Verificar se o formulário foi enviado
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=empresas');
        }
        
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        
        if (empty($nome)) {
            setFlashMessage('error', 'O nome da empresa é obrigatório.');
            redirect('index.php?route=empresas&action=edit&id=' . $id);
        }
        
        // Verificar se o nome já existe (exceto para a própria empresa)
        if ($this->empresaModel->nomeExiste($nome, $id)) {
            setFlashMessage('error', 'Já existe uma empresa com este nome.');
            redirect('index.php?route=empresas&action=edit&id=' . $id);
        }
        
        // Atualizar empresa
        $dados = [
            'nome' => $nome
        ];
        
        $resultado = $this->empresaModel->update($id, $dados);
        
        if ($resultado) {
            setFlashMessage('success', 'Empresa atualizada com sucesso.');
            redirect('index.php?route=empresas');
        } else {
            setFlashMessage('error', 'Erro ao atualizar empresa.');
            redirect('index.php?route=empresas&action=edit&id=' . $id);
        }
    }
    
    /**
     * Excluir empresa
     * 
     * @param int $id ID da empresa
     */
    public function delete($id) {
        // Verificar se o usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar se o usuário tem permissão de administrador
        if ($_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
            redirect('index.php?route=dashboard');
        }
        
        // Verificar se a empresa possui registros vinculados
        if ($this->empresaModel->possuiRegistrosVinculados($id)) {
            setFlashMessage('error', 'Não é possível excluir esta empresa porque existem registros vinculados a ela.');
            redirect('index.php?route=empresas');
        }
        
        // Excluir empresa
        $resultado = $this->empresaModel->delete($id);
        
        if ($resultado) {
            setFlashMessage('success', 'Empresa excluída com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao excluir empresa.');
        }
        
        redirect('index.php?route=empresas');
    }
}

