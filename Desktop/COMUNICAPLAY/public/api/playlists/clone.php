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
    
    // Busca a playlist original
    $playlistOriginal = $playlistModel->getById($id);
    if (!$playlistOriginal) {
        echo json_encode([
            'success' => false,
            'message' => 'Playlist original não encontrada.'
        ]);
        exit;
    }
    
    // Gera dados para a cópia
    $novoNome = $playlistOriginal['nome'] . ' (Cópia)';
    $novaDataInicio = date('Y-m-d H:i:s', strtotime($playlistOriginal['data_fim']) + 3600); // 1 hora após o fim
    $duracao = strtotime($playlistOriginal['data_fim']) - strtotime($playlistOriginal['data_inicio']);
    $novaDataFim = date('Y-m-d H:i:s', strtotime($novaDataInicio) + $duracao);
    $usuarioCriadorId = SessionManager::getUserId();
    
    // CORREÇÃO: Usar método create() em vez de clone() que não existe
    $novoId = $playlistModel->create(
        $novoNome, 
        $playlistOriginal['tela_id'], 
        $novaDataInicio, 
        $novaDataFim, 
        $usuarioCriadorId
    );
    
    if ($novoId) {
        // Busca as mídias da playlist original
        $midiasOriginais = $playlistModel->getMidias($id);
        
        // Copia as mídias para a nova playlist
        foreach ($midiasOriginais as $midia) {
            $playlistModel->addMidia(
                $novoId, 
                $midia['midia_id'], 
                $midia['ordem'], 
                $midia['tempo_exibicao']
            );
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Playlist clonada com sucesso.',
            'playlist_id' => $novoId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar nova playlist.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de clonar playlist: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

?>
