<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/includes/session.php';

$authController = new AuthController();
$email_to_clear = 'dsantos@ctdi.com';

$authController->publicClearLoginAttempts($email_to_clear);

echo "Tentativas de login para {$email_to_clear} foram limpas.";
?>
