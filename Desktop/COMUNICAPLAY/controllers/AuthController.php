<?php

require_once __DIR__ . 
'/../models/User.php';
require_once __DIR__ . 
'/../models/Database.php'; // Adicionado para acesso ao banco de dados
require_once __DIR__ . 
'/../includes/session.php';
require_once __DIR__ . 
'/../includes/functions.php';

class AuthController {
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->userModel = new User();
        $this->db = Database::getInstance();
    }
    
    public function login($email, $password) {
        try {
            // Validações básicas
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email e senha são obrigatórios.'
                ];
            }
            
            if (!validateEmail($email)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido.'
                ];
            }
            
            // Verifica tentativas de login (proteção contra força bruta)
            if ($this->isLoginBlocked($email)) {
                return [
                    'success' => false,
                    'message' => 'Muitas tentativas de login. Tente novamente em 15 minutos.'
                ];
            }
            
            // Tenta autenticar
            $user = $this->userModel->authenticate($email, $password);
            
            if ($user) {
                // Login bem-sucedido
                SessionManager::login($user);
                $this->clearLoginAttempts($email);
                
                logActivity('login_success', "Usuário {$user['nome']} fez login", $user['id']);
                
                return [
                    'success' => true,
                    'message' => 'Login realizado com sucesso.',
                    'user' => $user
                ];
            } else {
                // Login falhou
                $this->recordLoginAttempt($email);
                
                logActivity('login_failed', "Tentativa de login falhada para email: {$email}");
                
                return [
                    'success' => false,
                    'message' => 'Email ou senha incorretos.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    public function logout() {
        $user = SessionManager::getUser();
        
        if ($user) {
            logActivity('logout', "Usuário {$user['nome']} fez logout", $user['id']);
        }
        
        SessionManager::logout();
        
        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso.'
        ];
    }
    
    public function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        try {
            // Validações
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return [
                    'success' => false,
                    'message' => 'Todos os campos são obrigatórios.'
                ];
            }
            
            if ($newPassword !== $confirmPassword) {
                return [
                    'success' => false,
                    'message' => 'A nova senha e a confirmação não coincidem.'
                ];
            }
            
            if (!validatePassword($newPassword)) {
                return [
                    'success' => false,
                    'message' => 'A nova senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres.'
                ];
            }
            
            // Busca o usuário
            $user = $this->userModel->getById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ];
            }
            
            // Verifica a senha atual
            $stmt = $this->db->getConnection()->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($currentPassword, $userData['senha'])) {
                return [
                    'success' => false,
                    'message' => 'Senha atual incorreta.'
                ];
            }
            
            // Atualiza a senha
            if ($this->userModel->updatePassword($userId, $newPassword)) {
                logActivity('password_changed', "Usuário {$user['nome']} alterou a senha", $userId);
                
                return [
                    'success' => true,
                    'message' => 'Senha alterada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao alterar a senha. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ];
        }
    }
    
    private function isLoginBlocked($email) {
        $stmt = $this->db->getConnection()->prepare("
            SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt
            FROM login_attempts
            WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$email, LOGIN_LOCKOUT_TIME]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            return true;
        }
        return false;
    }
    
    private function recordLoginAttempt($email) {
        $stmt = $this->db->getConnection()->prepare("
            INSERT INTO login_attempts (email, ip_address, attempt_time)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    }
    
    private function clearLoginAttempts($email) {
        $stmt = $this->db->getConnection()->prepare("
            DELETE FROM login_attempts WHERE email = ?
        ");
        $stmt->execute([$email]);
    }
    
    public function checkSession() {
        return SessionManager::isLoggedIn();
    }
    
    public function getCurrentUser() {
        return SessionManager::getUser();
    }
    
    public function requireLogin() {
        SessionManager::requireLogin();
    }
    
    public function requireAdmin() {
        SessionManager::requireAdmin();
    }

    public function publicClearLoginAttempts($email) {
        $this->clearLoginAttempts($email);
    }
}

?>

