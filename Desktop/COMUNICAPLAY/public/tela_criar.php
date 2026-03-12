<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de criar tela
include __DIR__ . '/../views/tela_criar.php';

?>

