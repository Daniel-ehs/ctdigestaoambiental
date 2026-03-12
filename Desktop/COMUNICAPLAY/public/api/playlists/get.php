<?php

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../models/Playlist.php';

// Verifica se está logado
SessionManager::requireLogin();

// Define header JSON
header('Content-Type: application/json');

try {
    $playlistModel = new Playlist();
    
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID da playlist é obrigatório.'
        ]);
        exit;
    }
    
    // Busca a playlist
    $playlist = $playlistModel->getById($id);
    
    if ($playlist) {
        // Busca as mídias da playlist
        $midias = $playlistModel->getMidias($id);
        $playlist['midias'] = $midias;
        
        echo json_encode([
            'success' => true,
            'playlist' => $playlist
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Playlist não encontrada.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de buscar playlist: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

?>

