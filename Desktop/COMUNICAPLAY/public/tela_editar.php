<?php

require_once __DIR__ . '/../includes/session.php';

// Verifica se está logado
SessionManager::requireLogin();

// Verifica se o ID foi fornecido
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: telas.php');
    exit;
}

// Inclui a view de editar tela
include __DIR__ . '/../views/tela_editar.php';

?>

