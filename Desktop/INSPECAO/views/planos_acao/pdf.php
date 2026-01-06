<?php
// Incluir cabeçalho e verificar autenticação
include BASE_PATH . '/views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se o caminho do PDF foi passado pelo controller
if (!isset($pdfPath) || empty($pdfPath) || !file_exists($pdfPath)) {
    setFlashMessage('error', 'Erro: Arquivo PDF não encontrado ou não foi gerado corretamente.');
    // Tentar redirecionar para a inspeção se o ID do plano estiver disponível
    $redirectUrl = isset($plano) && isset($plano['inspecao_id']) ? 'index.php?route=inspecoes&action=view&id=' . $plano['inspecao_id'] : 'index.php?route=inspecoes';
    redirect($redirectUrl);
}

// Extrair nome do arquivo do caminho completo
$pdfFilename = basename($pdfPath);

// Construir URL para download (assumindo que PDFS_DIR está dentro de um diretório acessível pela web, como 'uploads')
// Ajuste esta lógica se a estrutura de diretórios for diferente
$relativePdfPath = str_replace(ROOT_DIR, '', $pdfPath); // Remove a parte do ROOT_DIR
$relativePdfPath = ltrim($relativePdfPath, '/'); // Remove barra inicial se houver

// Tenta obter a URL base do config.php, senão usa uma estimativa
$baseUrl = defined('SITE_URL') ? SITE_URL : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$baseUrl = rtrim($baseUrl, '/'); // Garante que não haja barra dupla

$pdfUrl = $baseUrl . '/' . $relativePdfPath; 

// Obter ID da inspeção para o botão de voltar
$inspecaoId = $plano['inspecao_id'] ?? null;

?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Plano de Ação Gerado</h1>
        <?php if ($inspecaoId): ?>
            <a href="index.php?route=inspecoes&action=view&id=<?php echo $inspecaoId; ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Voltar para Inspeção
            </a>
        <?php else: ?>
             <a href="index.php?route=inspecoes" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Voltar para Inspeções
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body text-center">
            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">Sucesso!</h4>
                <p>O PDF do Plano de Ação #<?php echo htmlspecialchars($plano['id'] ?? 'N/A'); ?> foi gerado com sucesso.</p>
                <hr>
                <p class="mb-0">Clique no botão abaixo para fazer o download.</p>
            </div>

            <a href="<?php echo htmlspecialchars($pdfUrl); ?>" class="btn btn-primary btn-lg mt-3" download="<?php echo htmlspecialchars($pdfFilename); ?>">
                <i class="fas fa-download me-2"></i> Baixar PDF (<?php echo htmlspecialchars($pdfFilename); ?>)
            </a>
             <br><br>
             <small class="text-muted">Se o download não iniciar, clique com o botão direito e escolha "Salvar link como...".</small>
             <br>
             <small class="text-muted">URL: <?php echo htmlspecialchars($pdfUrl); ?></small>


        </div>
    </div>
</div>

<?php include BASE_PATH . '/views/templates/footer.php'; ?>

