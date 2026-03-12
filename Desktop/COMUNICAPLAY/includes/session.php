<?php
ob_start();

require_once __DIR__ . 
'/../config/config.php';

class SessionManager {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenera o ID da sessão periodicamente para segurança
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        // Verifica timeout da sessão
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public static function login($user) {
        self::start();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_tipo'] = $user['tipo'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        session_regenerate_id(true);
    }
    
    public static function logout() {
        self::destroy();
    }
    
    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        
        // Remove o cookie da sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
    
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        
        if ($_SESSION['user_tipo'] !== 'administrador') {
            header('Location: /dashboard.php?error=access_denied');
            exit;
        }
    }
    
    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['user_nome'],
            'email' => $_SESSION['user_email'],
            'tipo' => $_SESSION['user_tipo']
        ];
    }
    
    public static function getUserId() {
        return self::isLoggedIn() ? $_SESSION['user_id'] : null;
    }
    
    public static function getUserType() {
        return self::isLoggedIn() ? $_SESSION['user_tipo'] : null;
    }
    
    public static function isAdmin() {
        return self::getUserType() === 'administrador';
    }
    
    public static function setFlashMessage($type, $message) {
        self::start();
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    public static function getFlashMessages() {
        self::start();
        
        if (isset($_SESSION['flash_messages'])) {
            $messages = $_SESSION['flash_messages'];
            unset($_SESSION['flash_messages']);
            return $messages;
        }
        
        return [];
    }

    public static function generateCsrfToken() {
        if (empty($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }
        return $_SESSION["csrf_token"];
    }

    public static function validateCsrfToken($token) {
        if (empty($token) || $token !== $_SESSION["csrf_token"]) {
            return false;
        }
        return true;
    }
}

