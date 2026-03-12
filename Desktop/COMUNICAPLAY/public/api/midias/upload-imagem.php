<?php

require_once __DIR__ . 
'/../../../includes/session.php';
require_once __DIR__ . 
'/../../../controllers/MidiaController.php';
require_once __DIR__ . 
'/../../../includes/functions.php'; // Adicionado para isValidImage e UPLOAD_MAX_SIZE

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
    
    // Verifica se foi enviado arquivo
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Nenhum arquivo de imagem foi enviado ou houve erro no upload. Código do erro: ' . ($_FILES['imagem']['error'] ?? 'N/A');
        error_log("Erro no upload de imagem (API): " . $errorMessage);
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
        exit;
    }
    
    $nome = $_POST['nome'] ?? null;
    $duracao = (int)($_POST['duracao'] ?? 10);
    $pastaId = $_POST['pasta_id'] ?? null;
    
    // Converte string vazia para null
    if (empty($pastaId)) {
        $pastaId = null;
    }
    
    // Validação de tipo e tamanho antes de chamar o controller
    if (!isValidImage($_FILES['imagem'])) {
        $errorMessage = 'Arquivo de imagem inválido. Verifique o tipo e a extensão.';
        error_log("Erro no upload de imagem (API): " . $errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }
    
    if ($_FILES['imagem']['size'] > UPLOAD_MAX_SIZE) {
        $errorMessage = 'Arquivo muito grande. Máximo permitido: ' . formatBytes(UPLOAD_MAX_SIZE);
        error_log("Erro no upload de imagem (API): " . $errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }

    $result = $midiaController->uploadImagem($_FILES["imagem"], $nome, $duracao, $pastaId);
    
    ob_clean(); // Limpa qualquer output inesperado antes de enviar o JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de upload de imagem: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage() // Exibir mensagem de erro para depuração
    ]);
}


