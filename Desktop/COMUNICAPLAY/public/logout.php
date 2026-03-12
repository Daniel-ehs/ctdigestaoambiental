<?php

require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();
$result = $authController->logout();

// Redireciona para a página de login com mensagem
header("Location: login.php");
exit;

?>
