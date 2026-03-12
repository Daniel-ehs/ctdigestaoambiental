<?php
/**
 * Página 404 - Não Encontrado
 */
require_once __DIR__ . '/../config/config.php';
$page_title = '404 - Página Não Encontrada';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="error-page text-center py-5">
    <h1 class="display-1">404</h1>
    <h2>Ops! Página não encontrada</h2>
    <p class="lead">O caminho que você tentou acessar não existe ou foi movido.</p>
    <a href="<?php echo SITE_URL; ?>/public/dashboard.php" class="btn btn-primary mt-4">
        Voltar para o Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
