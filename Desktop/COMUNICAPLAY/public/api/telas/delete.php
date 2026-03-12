<?php

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../controllers/TelaController.php';

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
    $telaController = new TelaController();
    
    $id = (int)($_POST['id'] ?? 0);
    
    // Validações básicas
    if (!$id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID da tela é obrigatório.'
        ]);
        exit;
    }
    
    $result = $telaController->delete($id);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de excluir tela: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

?>

