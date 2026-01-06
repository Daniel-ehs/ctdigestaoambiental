<?php
/**
 * Controlador de Cadastros
 */

class CadastrosController {
    private $setorModel;
    private $localModel;
    private $tipoApontamentoModel;
    private $usuarioModel;
    private $empresaModel;
    
    public function __construct() {
        require_once 'models/Setor.php';
        require_once 'models/Local.php';
        require_once 'models/TipoApontamento.php';
        require_once 'models/Usuario.php';
        require_once 'models/Empresa.php';
        
        $this->setorModel = new Setor();
        $this->localModel = new Local();
        $this->tipoApontamentoModel = new TipoApontamento();
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
    }
    
    /**
     * Listar cadastros por tipo
     * 
     * @param string $type Tipo de cadastro (setores, locais, tipos, usuarios, empresas)
     */
    public function index($type = 'setores') {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar permissão de administrador para usuários e empresas
        if (($type === 'usuarios' || $type === 'empresas') && $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=dashboard');
        }
        
        // Obter dados conforme o tipo
        $dados = [];
        
        switch ($type) {
            case 'setores':
                $dados = $this->setorModel->listar(false);
                break;
                
            case 'locais':
                $setores = $this->setorModel->listar();
                $dados = $this->localModel->listar(false);
                break;
                
            case 'tipos':
                $dados = $this->tipoApontamentoModel->listar(false);
                break;
                
            case 'usuarios':
                $dados = $this->usuarioModel->listar();
                $empresas = $this->empresaModel->getAll();
                break;
                
            case 'empresas':
                $dados = $this->empresaModel->getAll();
                break;
                
            default:
                redirect('index.php?route=dashboard');
        }
        
        // Exibir lista de cadastros
        include "views/cadastros/{$type}.php";
    }
    
    /**
     * Exibir formulário de criação de cadastro
     * 
     * @param string $type Tipo de cadastro (setores, locais, tipos, usuarios, empresas)
     */
    public function create($type) {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar permissão de administrador para usuários e empresas
        if (($type === 'usuarios' || $type === 'empresas') && $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=dashboard');
        }
        
        // Obter dados adicionais conforme o tipo
        switch ($type) {
            case 'locais':
                $setores = $this->setorModel->listar();
                break;
                
            case 'usuarios':
                $empresas = $this->empresaModel->getAll();
                break;
                
            case 'setores':
            case 'tipos':
            case 'empresas':
                // Nada adicional necessário
                break;
                
            default:
                redirect('index.php?route=dashboard');
        }
        
        // Exibir formulário de criação
        include "views/cadastros/{$type}_form.php";
    }
    
    /**
     * Processar criação de cadastro
     * 
     * @param string $type Tipo de cadastro (setores, locais, tipos, usuarios, empresas)
     */
    public function store($type) {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar permissão de administrador para usuários e empresas
        if (($type === 'usuarios' || $type === 'empresas') && $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=dashboard');
        }
        
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("index.php?route=cadastros&type={$type}");
        }
        
        // Processar conforme o tipo
        switch ($type) {
            case 'setores':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'descricao' => $_POST['descricao'] ?? null,
                    'ativo' => isset($_POST['ativo']) ? 1 : 0,
                    'empresa_id' => $_POST['empresa_id'] ?? null
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome']) || empty($dados['empresa_id'])) {
                    setFlashMessage('error', 'O nome do setor e a empresa são obrigatórios.');
                    redirect("index.php?route=cadastros&type={$type}&action=create");
                }
                
                // Criar setor
                $resultado = $this->setorModel->criar($dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Setor criado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao criar setor.');
                }
                break;
                
            case 'locais':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'setor_id' => $_POST['setor_id'] ?? null,
                    'descricao' => $_POST['descricao'] ?? null,
                    'ativo' => isset($_POST['ativo']) ? 1 : 0,
                    'empresa_id' => $_POST['empresa_id'] ?? null
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome']) || empty($dados['setor_id']) || empty($dados['empresa_id'])) {
                    setFlashMessage('error', 'O nome, o setor e a empresa são obrigatórios.');
                    redirect("index.php?route=cadastros&type={$type}&action=create");
                }
                
                // Criar local
                $resultado = $this->localModel->criar($dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Local criado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao criar local.');
                }
                break;
                
            case 'tipos':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'descricao' => $_POST['descricao'] ?? null,
                    'cor' => $_POST['cor'] ?? '#28a745',
                    'ativo' => isset($_POST['ativo']) ? 1 : 0
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome'])) {
                    setFlashMessage('error', 'O nome do tipo é obrigatório.');
                    redirect("index.php?route=cadastros&type={$type}&action=create");
                }
                
                // Criar tipo
                $resultado = $this->tipoApontamentoModel->criar($dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Tipo de apontamento criado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao criar tipo de apontamento.');
                }
                break;
                
            case 'usuarios':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'senha' => $_POST['senha'] ?? '',
                    'nivel_acesso' => $_POST['nivel_acesso'] ?? 'inspetor',
                    'empresa_id' => $_POST['empresa_id'] ?? null
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
                    setFlashMessage('error', 'Nome, email e senha são obrigatórios.');
                    redirect("index.php?route=cadastros&type={$type}&action=create");
                }
                
                // Criar usuário
                $resultado = $this->usuarioModel->criar($dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Usuário criado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao criar usuário. Verifique se o email já está em uso.');
                }
                break;
                
            case 'empresas':
                $dados = [
                    'nome' => $_POST['nome'] ?? ''
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome'])) {
                    setFlashMessage('error', 'O nome da empresa é obrigatório.');
                    redirect("index.php?route=cadastros&type={$type}&action=create");
                }
                
                // Verificar se o nome já existe
                if ($this->empresaModel->nomeExiste($dados['nome'])) {
                    setFlashMessage('error', 'Já existe uma empresa com este nome.');
                    redirect("index.php?route=cadastros&type={$type}&action=create");
                }
                
                // Criar empresa
                $resultado = $this->empresaModel->create($dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Empresa criada com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao criar empresa.');
                }
                break;
                
            default:
                redirect('index.php?route=dashboard');
        }
        
        redirect("index.php?route=cadastros&type={$type}");
    }
    
    /**
     * Exibir formulário de edição de cadastro
     * 
     * @param string $type Tipo de cadastro (setores, locais, tipos, usuarios, empresas)
     * @param int $id ID do registro
     */
    public function edit($type, $id) {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar permissão de administrador para usuários e empresas
        if (($type === 'usuarios' || $type === 'empresas') && $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=dashboard');
        }
        
        // Obter registro conforme o tipo
        $registro = null;
        
        switch ($type) {
            case 'setores':
                $registro = $this->setorModel->obterPorId($id);
                break;
                
            case 'locais':
                $registro = $this->localModel->obterPorId($id);
                $setores = $this->setorModel->listar();
                break;
                
            case 'tipos':
                $registro = $this->tipoApontamentoModel->obterPorId($id);
                break;
                
            case 'usuarios':
                $registro = $this->usuarioModel->obterPorId($id);
                $empresas = $this->empresaModel->getAll();
                break;
                
            case 'empresas':
                $registro = $this->empresaModel->getById($id);
                break;
                
            default:
                redirect('index.php?route=dashboard');
        }
        
        if (!$registro) {
            setFlashMessage('error', 'Registro não encontrado.');
            redirect("index.php?route=cadastros&type={$type}");
        }
        
        // Exibir formulário de edição
        include "views/cadastros/{$type}_form.php";
    }
    
    /**
     * Processar atualização de cadastro
     * 
     * @param string $type Tipo de cadastro (setores, locais, tipos, usuarios, empresas)
     * @param int $id ID do registro
     */
    public function update($type, $id) {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar permissão de administrador para usuários e empresas
        if (($type === 'usuarios' || $type === 'empresas') && $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=dashboard');
        }
        
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("index.php?route=cadastros&type={$type}");
        }
        
        // Processar conforme o tipo
        switch ($type) {
            case 'setores':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'descricao' => $_POST['descricao'] ?? null,
                    'ativo' => isset($_POST['ativo']) ? 1 : 0
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome'])) {
                    setFlashMessage('error', 'O nome do setor é obrigatório.');
                    redirect("index.php?route=cadastros&type={$type}&action=edit&id={$id}");
                }
                
                // Atualizar setor
                $resultado = $this->setorModel->atualizar($id, $dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Setor atualizado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao atualizar setor.');
                }
                break;
                
            case 'locais':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'setor_id' => $_POST['setor_id'] ?? null,
                    'descricao' => $_POST['descricao'] ?? null,
                    'ativo' => isset($_POST['ativo']) ? 1 : 0
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome']) || empty($dados['setor_id'])) {
                    setFlashMessage('error', 'O nome e o setor são obrigatórios.');
                    redirect("index.php?route=cadastros&type={$type}&action=edit&id={$id}");
                }
                
                // Atualizar local
                $resultado = $this->localModel->atualizar($id, $dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Local atualizado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao atualizar local.');
                }
                break;
                
            case 'tipos':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'descricao' => $_POST['descricao'] ?? null,
                    'cor' => $_POST['cor'] ?? '#28a745',
                    'ativo' => isset($_POST['ativo']) ? 1 : 0
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome'])) {
                    setFlashMessage('error', 'O nome do tipo é obrigatório.');
                    redirect("index.php?route=cadastros&type={$type}&action=edit&id={$id}");
                }
                
                // Atualizar tipo
                $resultado = $this->tipoApontamentoModel->atualizar($id, $dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Tipo de apontamento atualizado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao atualizar tipo de apontamento.');
                }
                break;
                
            case 'usuarios':
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'nivel_acesso' => $_POST['nivel_acesso'] ?? 'inspetor',
                    'empresa_id' => $_POST['empresa_id'] ?? null
                ];
                
                // Adicionar senha apenas se fornecida
                if (!empty($_POST['senha'])) {
                    $dados['senha'] = $_POST['senha'];
                }
                
                // Validar campos obrigatórios
                if (empty($dados['nome']) || empty($dados['email'])) {
                    setFlashMessage('error', 'Nome e email são obrigatórios.');
                    redirect("index.php?route=cadastros&type={$type}&action=edit&id={$id}");
                }
                
                // Atualizar usuário
                $resultado = $this->usuarioModel->atualizar($id, $dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Usuário atualizado com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao atualizar usuário. Verifique se o email já está em uso.');
                }
                break;
                
            case 'empresas':
                $dados = [
                    'nome' => $_POST['nome'] ?? ''
                ];
                
                // Validar campos obrigatórios
                if (empty($dados['nome'])) {
                    setFlashMessage('error', 'O nome da empresa é obrigatório.');
                    redirect("index.php?route=cadastros&type={$type}&action=edit&id={$id}");
                }
                
                // Verificar se o nome já existe (exceto para a própria empresa)
                if ($this->empresaModel->nomeExiste($dados['nome'], $id)) {
                    setFlashMessage('error', 'Já existe uma empresa com este nome.');
                    redirect("index.php?route=cadastros&type={$type}&action=edit&id={$id}");
                }
                
                // Atualizar empresa
                $resultado = $this->empresaModel->update($id, $dados);
                
                if ($resultado) {
                    setFlashMessage('success', 'Empresa atualizada com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao atualizar empresa.');
                }
                break;
                
            default:
                redirect('index.php?route=dashboard');
        }
        
        redirect("index.php?route=cadastros&type={$type}");
    }
    
    /**
     * Processar exclusão de cadastro
     * 
     * @param string $type Tipo de cadastro (setores, locais, tipos, usuarios, empresas)
     * @param int $id ID do registro
     */
    public function delete($type, $id) {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            redirect('index.php?route=login');
        }
        
        // Verificar permissão de administrador para usuários e empresas
        if (($type === 'usuarios' || $type === 'empresas') && $_SESSION['user_nivel'] !== 'admin') {
            setFlashMessage('error', 'Acesso negado.');
            redirect('index.php?route=dashboard');
        }
        
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("index.php?route=cadastros&type={$type}");
        }
        
        // Processar conforme o tipo
        switch ($type) {
            case 'setores':
                // Verificar se existem locais vinculados
                if ($this->setorModel->possuiLocaisVinculados($id)) {
                    setFlashMessage('error', 'Não é possível excluir este setor porque existem locais vinculados a ele.');
                    redirect("index.php?route=cadastros&type={$type}");
                }
                
                // Excluir setor
                $resultado = $this->setorModel->excluir($id);
                
                if ($resultado) {
                    setFlashMessage('success', 'Setor excluído com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao excluir setor.');
                }
                break;
                
            case 'locais':
                // Verificar se existem inspeções vinculadas
                if ($this->localModel->possuiInspecoesVinculadas($id)) {
                    setFlashMessage('error', 'Não é possível excluir este local porque existem inspeções vinculadas a ele.');
                    redirect("index.php?route=cadastros&type={$type}");
                }
                
                // Excluir local
                $resultado = $this->localModel->excluir($id);
                
                if ($resultado) {
                    setFlashMessage('success', 'Local excluído com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao excluir local.');
                }
                break;
                
            case 'tipos':
                // Verificar se existem inspeções vinculadas
                if ($this->tipoApontamentoModel->possuiInspecoesVinculadas($id)) {
                    setFlashMessage('error', 'Não é possível excluir este tipo porque existem inspeções vinculadas a ele.');
                    redirect("index.php?route=cadastros&type={$type}");
                }
                
                // Excluir tipo
                $resultado = $this->tipoApontamentoModel->excluir($id);
                
                if ($resultado) {
                    setFlashMessage('success', 'Tipo de apontamento excluído com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao excluir tipo de apontamento.');
                }
                break;
                
            case 'usuarios':
                // Verificar se é o próprio usuário
                if ($id == $_SESSION['user_id']) {
                    setFlashMessage('error', 'Não é possível excluir o próprio usuário.');
                    redirect("index.php?route=cadastros&type={$type}");
                }
                
                // Verificar se existem inspeções vinculadas
                if ($this->usuarioModel->possuiInspecoesVinculadas($id)) {
                    setFlashMessage('error', 'Não é possível excluir este usuário porque existem inspeções vinculadas a ele.');
                    redirect("index.php?route=cadastros&type={$type}");
                }
                
                // Excluir usuário
                $resultado = $this->usuarioModel->excluir($id);
                
                if ($resultado) {
                    setFlashMessage('success', 'Usuário excluído com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao excluir usuário.');
                }
                break;
                
            case 'empresas':
                // Verificar se existem registros vinculados
                if ($this->empresaModel->possuiRegistrosVinculados($id)) {
                    setFlashMessage('error', 'Não é possível excluir esta empresa porque existem registros vinculados a ela.');
                    redirect("index.php?route=cadastros&type={$type}");
                }
                
                // Excluir empresa
                $resultado = $this->empresaModel->delete($id);
                
                if ($resultado) {
                    setFlashMessage('success', 'Empresa excluída com sucesso.');
                } else {
                    setFlashMessage('error', 'Erro ao excluir empresa.');
                }
                break;
                
            default:
                redirect('index.php?route=dashboard');
        }
        
        redirect("index.php?route=cadastros&type={$type}");
    }
}

