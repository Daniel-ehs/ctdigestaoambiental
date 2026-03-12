<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de edição de mídia
include __DIR__ . '/../views/midia_editar.php';

?>

