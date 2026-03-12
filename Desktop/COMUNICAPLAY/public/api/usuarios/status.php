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

$id = $_POST['id'] ?? null;
$acao = $_POST['acao'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
    exit;
}

if (!in_array($acao, ['ativar', 'desativar'])) {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit;
}

// Verificar se não está tentando desativar a si mesmo
$currentUser = SessionManager::getUser();
if ($currentUser['id'] == $id) {
    echo json_encode(['success' => false, 'message' => 'Você não pode alterar seu próprio status']);
    exit;
}

try {
    $userController = new UserController();
    
    // Buscar dados do usuário
    $userResult = $userController->getById($id);
    if (!$userResult['success']) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }
    
    $user = $userResult['user'];
    $novoStatus = ($acao === 'ativar') ? 1 : 0;
    
    $result = $userController->update($id, $user['nome'], $user['email'], $user['tipo'], $novoStatus);
    
    if ($result['success']) {
        $mensagem = ($acao === 'ativar') ? 'Usuário ativado com sucesso' : 'Usuário desativado com sucesso';
        echo json_encode([
            'success' => true,
            'message' => $mensagem
        ]);
    } else {
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Erro ao alterar status: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>

