<?php
/**
 * Controlador de Projetos
 * VERSÃO CORRIGIDA (2ª Tentativa) - Lógica de ID revisada
 */

class ProjetoController {
    private $projetoModel;
    private $empresaModel;
    
    public function __construct() {
        require_once 'models/Projeto.php';
        require_once 'models/Empresa.php';
        
        $this->projetoModel = new Projeto(Database::getInstance()->getConnection());
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Listar projetos (sem alterações, já estava funcional)
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        $empresas = $this->empresaModel->getAll();
        
        $empresaId = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;
        $usuarioEmpresaId = $_SESSION['user_empresa_id'] ?? null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId) {
            $empresaId = $usuarioEmpresaId;
        }
        
        $filtros = [
            'status' => $_GET['status'] ?? null,
            'prazo_inicio' => $_GET['prazo_inicio'] ?? null,
            'prazo_fim' => $_GET['prazo_fim'] ?? null,
            'empresa_id' => $empresaId,
            'usuario_empresa_id' => $usuarioEmpresaId,
            'usuario_nivel' => $usuarioNivel
        ];
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 10;
        
        $projetos = $this->projetoModel->listar($filtros, $pagina, $porPagina);
        
        $totalProjetos = $this->projetoModel->contar($filtros);
        $totalPaginas = ceil($totalProjetos / $porPagina);
        
        include 'views/projetos/index.php';
    }
    
    /**
     * Exibir formulário de criação de projeto
     */
    public function create() {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        $empresas = $this->empresaModel->getAll();
        include 'views/projetos/create.php';
    }
    
    /**
     * Processar criação de projeto
     */
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=projetos');
        }
        
        $empresaId = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : null;
        $usuarioEmpresaId = $_SESSION['user_empresa_id'] ?? null;
        $usuarioNivel = $_SESSION['user_nivel'] ?? '';
        
        if ($usuarioNivel !== 'admin' && $usuarioEmpresaId && $empresaId != $usuarioEmpresaId) {
            setFlashMessage('error', 'Você não tem permissão para criar projetos para esta empresa.');
            redirect('index.php?route=projetos');
            return;
        }
        
        $dados = [
            'fonte' => $_POST['fonte'] ?? null,
            'descricao' => $_POST['descricao'] ?? null,
            'prazo' => !empty($_POST['prazo']) ? $_POST['prazo'] : null,
            'status' => $_POST['status'] ?? 'Em Andamento',
            'observacao' => $_POST['observacao'] ?? null,
            'usuario_id' => $_SESSION['user_id'],
            'empresa_id' => $empresaId
        ];
        
        if (empty($dados['descricao'])) {
            setFlashMessage('error', 'A descrição do projeto é obrigatória.');
            redirect('index.php?route=projetos&action=create');
            return;
        }
        
        if ($this->projetoModel->criar($dados)) {
            setFlashMessage('success', 'Projeto registrado com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao registrar projeto.');
        }
        redirect('index.php?route=projetos');
    }

    /**
     * Exibir detalhes de um projeto
     */
    public function view() {
        // CORREÇÃO: Pega o ID diretamente do GET
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlashMessage('error', 'ID do projeto não fornecido.');
            redirect('index.php?route=projetos');
            return;
        }

        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $projeto = $this->projetoModel->obterPorId($id);

        if (!$projeto) {
            setFlashMessage("error", "Projeto não encontrado.");
            redirect("index.php?route=projetos");
            return;
        }

        $usuarioEmpresaId = $_SESSION["user_empresa_id"] ?? null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? "";

        if ($usuarioNivel !== "admin" && $usuarioEmpresaId && $projeto["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para visualizar este projeto.");
            redirect("index.php?route=projetos");
            return;
        }

        include "views/projetos/view.php";
    }

    /**
     * Exibir formulário de edição de projeto
     */
    public function edit() {
        // CORREÇÃO: Pega o ID diretamente do GET
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlashMessage('error', 'ID do projeto não fornecido.');
            redirect('index.php?route=projetos');
            return;
        }

        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $projeto = $this->projetoModel->obterPorId($id);

        if (!$projeto) {
            setFlashMessage("error", "Projeto não encontrado.");
            redirect("index.php?route=projetos");
            return;
        }

        $empresas = $this->empresaModel->getAll();

        $usuarioEmpresaId = $_SESSION["user_empresa_id"] ?? null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? "";

        if ($usuarioNivel !== "admin" && $usuarioEmpresaId && $projeto["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para editar este projeto.");
            redirect("index.php?route=projetos");
            return;
        }
        
        include "views/projetos/edit.php";
    }

    /**
     * Processar atualização de projeto
     */
    public function update() {
        // CORREÇÃO: Pega o ID diretamente do GET
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlashMessage('error', 'ID do projeto não fornecido.');
            redirect('index.php?route=projetos');
            return;
        }

        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=projetos');
            return;
        }

        $projetoExistente = $this->projetoModel->obterPorId($id);

        if (!$projetoExistente) {
            setFlashMessage("error", "Projeto não encontrado.");
            redirect("index.php?route=projetos");
            return;
        }

        $empresaId = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : null;
        $usuarioEmpresaId = $_SESSION["user_empresa_id"] ?? null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? "";

        if ($usuarioNivel !== "admin" && $usuarioEmpresaId && ($empresaId != $usuarioEmpresaId || $projetoExistente["empresa_id"] != $usuarioEmpresaId)) {
            setFlashMessage("error", "Você não tem permissão para atualizar este projeto.");
            redirect("index.php?route=projetos");
            return;
        }

        $dados = [
            'fonte' => $_POST['fonte'] ?? null,
            'descricao' => $_POST['descricao'] ?? null,
            'prazo' => !empty($_POST['prazo']) ? $_POST['prazo'] : null,
            'status' => $_POST['status'] ?? 'Em Andamento',
            'observacao' => $_POST['observacao'] ?? null,
            'empresa_id' => $empresaId
        ];

        if (empty($dados['descricao'])) {
            setFlashMessage('error', 'A descrição do projeto é obrigatória.');
            redirect('index.php?route=projetos&action=edit&id=' . $id);
            return;
        }

        if ($this->projetoModel->atualizar($id, $dados)) {
            setFlashMessage('success', 'Projeto atualizado com sucesso.');
            redirect('index.php?route=projetos&action=view&id=' . $id);
        } else {
            setFlashMessage('error', 'Erro ao atualizar projeto.');
            redirect('index.php?route=projetos&action=edit&id=' . $id);
        }
    }

    /**
     * Processar exclusão de projeto
     */
    public function delete() {
        // CORREÇÃO: Pega o ID diretamente do GET
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlashMessage('error', 'ID do projeto não fornecido.');
            redirect('index.php?route=projetos');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=projetos');
            return;
        }
        
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        $projeto = $this->projetoModel->obterPorId($id);

        if (!$projeto) {
            setFlashMessage("error", "Projeto não encontrado.");
            redirect("index.php?route=projetos");
            return;
        }

        $usuarioEmpresaId = $_SESSION["user_empresa_id"] ?? null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? "";

        if ($usuarioNivel !== "admin" && $usuarioEmpresaId && $projeto["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para excluir este projeto.");
            redirect("index.php?route=projetos");
            return;
        }
        
        if ($this->projetoModel->excluir($id)) {
            setFlashMessage('success', 'Projeto excluído com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao excluir projeto.');
        }
        redirect('index.php?route=projetos');
    }

    /**
     * Concluir um projeto
     */
    public function concluir() {
        // CORREÇÃO: Pega o ID diretamente do GET
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlashMessage('error', 'ID do projeto não fornecido.');
            redirect('index.php?route=projetos');
            return;
        }

        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=projetos');
            return;
        }

        $projeto = $this->projetoModel->obterPorId($id);

        if (!$projeto) {
            setFlashMessage("error", "Projeto não encontrado.");
            redirect("index.php?route=projetos");
            return;
        }

        $usuarioEmpresaId = $_SESSION["user_empresa_id"] ?? null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? "";

        if ($usuarioNivel !== "admin" && $usuarioEmpresaId && $projeto["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para concluir este projeto.");
            redirect("index.php?route=projetos");
            return;
        }

        $dataConclusao = $_POST['data_conclusao'] ?? date('Y-m-d');

        if (empty($dataConclusao)) {
            setFlashMessage('error', 'A data de conclusão é obrigatória.');
            redirect('index.php?route=projetos');
            return;
        }
        
        if ($this->projetoModel->concluir($id, $dataConclusao)) {
            setFlashMessage('success', 'Projeto concluído com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao concluir projeto.');
        }
        redirect('index.php?route=projetos');
    }

    /**
     * Cancelar um projeto
     */
    public function cancelar() {
        // CORREÇÃO: Pega o ID diretamente do GET
        $id = $_GET['id'] ?? null;
        if (!$id) {
            setFlashMessage('error', 'ID do projeto não fornecido.');
            redirect('index.php?route=projetos');
            return;
        }
        
        if (!isset($_SESSION["user_id"])) {
            redirect("index.php?route=login");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?route=projetos');
            return;
        }

        $projeto = $this->projetoModel->obterPorId($id);

        if (!$projeto) {
            setFlashMessage("error", "Projeto não encontrado.");
            redirect("index.php?route=projetos");
            return;
        }

        $usuarioEmpresaId = $_SESSION["user_empresa_id"] ?? null;
        $usuarioNivel = $_SESSION["user_nivel"] ?? "";

        if ($usuarioNivel !== "admin" && $usuarioEmpresaId && $projeto["empresa_id"] != $usuarioEmpresaId) {
            setFlashMessage("error", "Você não tem permissão para cancelar este projeto.");
            redirect("index.php?route=projetos");
            return;
        }

        if ($this->projetoModel->cancelar($id)) {
            setFlashMessage('success', 'Projeto cancelado com sucesso.');
        } else {
            setFlashMessage('error', 'Erro ao cancelar projeto.');
        }
        redirect("index.php?route=projetos");
    }
}
