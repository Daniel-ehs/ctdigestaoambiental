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
    
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $resolucao = $_POST['resolucao'] ?? '1920x1080';
    $localizacao = $_POST['localizacao'] ?? '';
    $comMoldura = isset($_POST['com_moldura']) ? (int)$_POST['com_moldura'] : 0;
    
    // Validações básicas
    if (empty($nome)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nome da tela é obrigatório.'
        ]);
        exit;
    }
    
    $result = $telaController->create($nome, $descricao, $resolucao, $localizacao, $comMoldura);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de criar tela: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

?>

