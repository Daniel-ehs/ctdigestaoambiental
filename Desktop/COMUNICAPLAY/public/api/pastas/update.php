<?php

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../controllers/MidiaController.php';

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
    $midiaController = new MidiaController();
    
    $id = $_GET['id'] ?? null;
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $pastaPaiId = $_POST['pasta_pai_id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID da pasta é obrigatório']);
        exit;
    }
    
    // Converte string vazia para null
    if (empty($pastaPaiId)) {
        $pastaPaiId = null;
    }
    
    $result = $midiaController->updatePasta($id, $nome, $descricao, $pastaPaiId);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de atualizar pasta: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

?>

