<?php

require_once __DIR__ . 
'/../../../includes/session.php';
require_once __DIR__ . 
'/../../../controllers/MidiaController.php';
require_once __DIR__ . 
'/../../../includes/functions.php'; // Adicionado para isValidVideo e UPLOAD_MAX_SIZE

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
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Nenhum arquivo de vídeo foi enviado ou houve erro no upload. Código do erro: ' . ($_FILES['video']['error'] ?? 'N/A');
        error_log("Erro no upload de vídeo (API): " . $errorMessage);
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
        exit;
    }
    
    $nome = $_POST['nome'] ?? null;
    $duracao = (int)($_POST['duracao'] ?? 0);
    
    // Se a duração não foi fornecida ou é 0, tenta detectar automaticamente
    if ($duracao === 0 && isset($_FILES['video']['tmp_name'])) {
        $duracao = getVideoDuration($_FILES['video']['tmp_name']);
    }
    $pastaId = $_POST['pasta_id'] ?? null;
    
    // Converte string vazia para null
    if (empty($pastaId)) {
        $pastaId = null;
    }
    
    // Validação de tipo e tamanho antes de chamar o controller
    if (!isValidVideo($_FILES['video'])) {
        $errorMessage = 'Arquivo de vídeo inválido. Verifique o tipo e a extensão.';
        error_log("Erro no upload de vídeo (API): " . $errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }
    
    if ($_FILES['video']['size'] > UPLOAD_MAX_SIZE) {
        $errorMessage = 'Arquivo muito grande. Máximo permitido: ' . formatBytes(UPLOAD_MAX_SIZE);
        error_log("Erro no upload de vídeo (API): " . $errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }

    $result = $midiaController->uploadVideo($_FILES["video"], $nome, $duracao, $pastaId);
    
    ob_clean(); // Limpa qualquer output inesperado antes de enviar o JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Erro na API de upload de vídeo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage() // Exibir mensagem de erro para depuração
    ]);
}


