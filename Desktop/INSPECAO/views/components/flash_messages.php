<?php
/**
 * Componente para exibição de mensagens flash
 */

// Verificar se existem mensagens flash
if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'];
    
    // Definir classe de alerta com base no tipo
    $alertClass = 'alert-info';
    $icon = 'fa-info-circle';
    
    switch ($type) {
        case 'success':
            $alertClass = 'alert-success';
            $icon = 'fa-check-circle';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            $icon = 'fa-exclamation-circle';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            $icon = 'fa-exclamation-triangle';
            break;
    }
    
    // Exibir mensagem
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo '<i class="fas ' . $icon . ' me-2"></i>' . htmlspecialchars($message);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>';
    echo '</div>';
    
    // Limpar mensagens flash
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?>
