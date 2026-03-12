<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/Playlist.php';
require_once __DIR__ . '/../models/Tela.php';

try {
    // Recebe parâmetros
    $hash = $_GET['hash'] ?? '';
    $lastUpdate = $_GET['last_update'] ?? '';
    $currentPlaylistId = $_GET['current_playlist_id'] ?? '';
    
    if (empty($hash)) {
        throw new Exception('Hash da tela não fornecido');
    }
    
    // Busca a tela pelo hash
    $telaModel = new Tela();
    $tela = $telaModel->getByHash($hash);
    
    if (!$tela) {
        throw new Exception('Tela não encontrada');
    }
    
    // Busca playlist ativa atual
    $playlistModel = new Playlist();
    $playlist = $playlistModel->getPlaylistAtiva($tela['id']);
    
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'tela_id' => $tela['id'],
        'tela_nome' => $tela['nome']
    ];
    
    if (!$playlist) {
        // Não há playlist ativa
        $response['has_playlist'] = false;
        $response['playlist_id'] = null;
        $response['changed'] = ($currentPlaylistId !== '');
        $response['midias'] = [];
        $response['total_midias'] = 0;
        
        echo json_encode($response);
        exit();
    }
    
    // Há playlist ativa
    $response['has_playlist'] = true;
    $response['playlist_id'] = $playlist['id'];
    $response['playlist_nome'] = $playlist['nome'];
    $response['total_midias'] = $playlist['total_midias'];
    
    // Verifica se a playlist mudou
    $playlistChanged = ($currentPlaylistId !== $playlist['id']);
    
    // Verifica se houve atualização na playlist (se fornecido timestamp)
    $contentChanged = false;
    if (!empty($lastUpdate) && !$playlistChanged) {
        // Compara timestamp de última atualização
        $playlistUpdateTime = $playlist['updated_at'] ?? $playlist['created_at'] ?? '';
        if (!empty($playlistUpdateTime)) {
            $contentChanged = (strtotime($playlistUpdateTime) > strtotime($lastUpdate));
        }
    }
    
    $response['changed'] = $playlistChanged || $contentChanged;
    $response['playlist_changed'] = $playlistChanged;
    $response['content_changed'] = $contentChanged;
    
    // Se houve mudança, busca as mídias
    if ($response['changed'] || empty($currentPlaylistId)) {
        $midias = [];
        if ($playlist['total_midias'] > 0) {
            $midias = $playlistModel->getMidias($playlist['id']);
        }
        
        $response['midias'] = $midias;
        $response['last_update'] = date('Y-m-d H:i:s');
        
        // Log da mudança
        error_log("Playlist atualizada para tela {$tela['nome']}: " . 
                 ($playlistChanged ? "Nova playlist: {$playlist['nome']}" : "Conteúdo atualizado") . 
                 " - {$playlist['total_midias']} mídias");
    } else {
        // Sem mudanças
        $response['midias'] = null;
        $response['last_update'] = $lastUpdate;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erro ao verificar mudanças na playlist: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>

