<?php

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../models/Midia.php';

// Verifica se está logado
SessionManager::requireLogin();

// Define header JSON
header('Content-Type: application/json');

try {
    $midiaModel = new Midia();
    
    $id = $_GET['id'] ?? $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID da mídia é obrigatório.'
        ]);
        exit;
    }
    
    // Verifica se a mídia existe
    $midia = $midiaModel->getById($id);
    if (!$midia) {
        echo json_encode([
            'success' => false,
            'message' => 'Mídia não encontrada.'
        ]);
        exit;
    }
    
    // Deleta a mídia
    if ($midiaModel->delete($id)) {
        echo json_encode([
            'success' => true,
            'message' => 'Mídia deletada com sucesso.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao deletar mídia.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de excluir mídia: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

?>
