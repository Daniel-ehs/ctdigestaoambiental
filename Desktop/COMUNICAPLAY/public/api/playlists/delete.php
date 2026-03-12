<?php

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../models/Playlist.php';

// Verifica se está logado
SessionManager::requireLogin();

// Define header JSON
header('Content-Type: application/json');

try {
    $playlistModel = new Playlist();
    
    $id = $_GET['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID da playlist é obrigatório.'
        ]);
        exit;
    }
    
    // Verifica se a playlist existe
    $playlist = $playlistModel->getById($id);
    if (!$playlist) {
        echo json_encode([
            'success' => false,
            'message' => 'Playlist não encontrada.'
        ]);
        exit;
    }
    
    // Deleta a playlist
    if ($playlistModel->delete($id)) {
        echo json_encode([
            'success' => true,
            'message' => 'Playlist deletada com sucesso.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao deletar playlist.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de excluir playlist: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

?>

