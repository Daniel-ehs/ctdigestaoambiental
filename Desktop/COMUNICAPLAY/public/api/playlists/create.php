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
    
    $nome = $_POST['nome'] ?? '';
    $telaId = $_POST['tela_id'] ?? '';
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';
    $midias = isset($_POST['midias']) ? json_decode($_POST['midias'], true) : [];
    
    // Validações básicas
    if (empty($nome)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nome da playlist é obrigatório.'
        ]);
        exit;
    }
    
    if (empty($telaId)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tela é obrigatória.'
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
    
    $result = $playlistController->create($nome, $telaId, $dataInicio, $dataFim, $midias);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de criar playlist: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

?>

