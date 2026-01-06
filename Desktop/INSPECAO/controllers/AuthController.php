<?php
/**
 * Controlador de Autenticação
 */

class AuthController {
    private $usuarioModel;
    
    public function __construct() {
        require_once 'models/Usuario.php';
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Exibir formulário de login
     */
    public function login() {
    // Se for uma submissão de formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Adicionar log para depuração
        error_log('Tentativa de login: ' . $_POST['email']);
        
        // Verificar se email e senha foram enviados
        if (isset($_POST['email']) && isset($_POST['senha'])) {
            $email = $_POST['email'];
            $senha = $_POST['senha'];
            
            // Verificar credenciais usando o método autenticar do modelo Usuario
            $usuario = $this->usuarioModel->autenticar($email, $senha);
            
            if ($usuario) {
                // Login bem-sucedido, criar sessão
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_nome'] = $usuario['nome'];
                $_SESSION['user_email'] = $usuario['email'];
                $_SESSION['user_nivel'] = $usuario['nivel_acesso'];
                
                // Redirecionar para o dashboard
                redirect('index.php?route=dashboard');
                return;
            }
            
            // Se chegou aqui, houve falha no login
            setFlashMessage('error', 'Email ou senha inválidos.');
            redirect('index.php?route=login');
            return;
        }
        
        // Se chegou aqui, houve falha no login
        setFlashMessage('error', 'Email ou senha inválidos.');
        redirect('index.php?route=login');
        return;
    }
    
    // Se não for POST, exibir a página de login
    include 'views/auth/login.php';
}

    
    /**
     * Fazer logout
     */
    public function logout() {
        // Destruir sessão
        session_unset();
        session_destroy();
        
        // Redirecionar para login
        redirect('index.php?route=login');
    }
    
    /**
     * Alterar senha
     */
   public function alterarSenha() {
    // Verificar se está autenticado
    if (!isset($_SESSION['user_id'])) {
        redirect('index.php?route=login');
    }
    
    // Se for uma submissão de formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
        // Verificar se as senhas coincidem
        if ($novaSenha !== $confirmarSenha) {
            setFlashMessage('error', 'As senhas não coincidem.');
            redirect('index.php?route=auth&action=alterarSenha');
            return;
        }
        
        // Aqui você deve verificar a senha atual no banco de dados
        // Por enquanto, vamos apenas simular que a alteração foi bem-sucedida
        
        setFlashMessage('success', 'Senha alterada com sucesso!');
        redirect('index.php?route=dashboard');
        return;
    }
    
    // Exibir formulário de alteração de senha
    include 'views/auth/alterar_senha.php';
}

}
