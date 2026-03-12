<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
if (SessionManager::isLoggedIn()) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}

exit;

?>

