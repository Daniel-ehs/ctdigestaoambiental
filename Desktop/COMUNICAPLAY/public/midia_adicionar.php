<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de adicionar mídia
include __DIR__ . '/../views/midia_adicionar.php';

?>

