<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Inclui a view de edição de playlists
include __DIR__ . '/../views/playlist_editar.php';

?>
