<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de criar playlist
include __DIR__ . '/../views/playlist_criar.php';

?>

