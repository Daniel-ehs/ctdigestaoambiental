<?php

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../controllers/PlaylistController.php';

// Verifica se está logado
SessionManager::requireLogin();

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Define header JSON
header('Content-Type: application/json');

try {
    $playlistController = new PlaylistController();
    
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';
    $midias = $_POST['midias'] ?? null;
    
    // Validações básicas
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID da playlist é obrigatório.'
        ]);
        exit;
    }
    
    if (empty($nome)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nome da playlist é obrigatório.'
        ]);
        exit;
    }
    
    if (empty($dataInicio)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data de início é obrigatória.'
        ]);
        exit;
    }
    
    if (empty($dataFim)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data de fim é obrigatória.'
        ]);
        exit;
    }
    
    // Converte formato de data para o formato esperado pelo controller
    $dataInicio = date('Y-m-d H:i:s', strtotime($dataInicio));
    $dataFim = date('Y-m-d H:i:s', strtotime($dataFim));
    
    $result = $playlistController->update($id, $nome, $dataInicio, $dataFim, $midias);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de atualizar playlist: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

?>

