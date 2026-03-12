<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../controllers/UserController.php';

SessionManager::start();

// Verificar se o usuário está logado
if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$userId = $_POST['userId'] ?? null;
$telaId = $_POST['telaId'] ?? null;

if (!$userId || !$telaId) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário e da tela são obrigatórios']);
    exit;
}

try {
    $userController = new UserController();
    $result = $userController->associateToTela($userId, $telaId);
    
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Erro ao vincular tela: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>

