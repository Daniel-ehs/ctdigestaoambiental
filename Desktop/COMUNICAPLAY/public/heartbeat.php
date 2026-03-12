<?php

require_once __DIR__ . '/../controllers/TelaController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Verifica se o hash da tela foi fornecido
    $hash = $_GET['tela'] ?? '';
    
    if (empty($hash)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Hash da tela não fornecido.'
        ]);
        exit;
    }
    
    $telaController = new TelaController();
    $result = $telaController->updateHeartbeat($hash);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Heartbeat registrado.',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    error_log("Erro no heartbeat: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor.'
    ]);
}

?>

