<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view do dashboard
include __DIR__ . '/../views/dashboard.php';

?>

