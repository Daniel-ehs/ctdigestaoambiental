<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de usuários
include __DIR__ . '/../views/usuarios.php';

?>

