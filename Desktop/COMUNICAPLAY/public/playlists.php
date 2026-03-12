<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de playlists
include __DIR__ . '/../views/playlists.php';

?>

