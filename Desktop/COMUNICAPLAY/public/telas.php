<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de telas
include __DIR__ . '/../views/telas.php';

?>

