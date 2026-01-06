<?php
// Incluir cabeçalho
include 'views/templates/header.php';

// Verificar se está autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}

// Verificar se o usuário tem permissão de administrador
if ($_SESSION['user_nivel'] !== 'admin') {
    setFlashMessage('error', 'Você não tem permissão para acessar esta página.');
    redirect('index.php?route=dashboard');
}

// Verificar se a empresa foi carregada
if (!isset($empresa) || !$empresa) {
    setFlashMessage('error', 'Empresa não encontrada.');
    redirect('index.php?route=empresas');
}
?>

<div class="container-fluid">
    <!-- Título da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Empresa</h1>
        <a href="index.php?route=empresas" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Voltar
        </a>
    </div>

    <!-- Mensagens de Feedback -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Card do Formulário -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulário de Edição</h6>
        </div>
        <div class="card-body">
            <form method="post" action="index.php?route=empresas&action=update&id=<?php echo $empresa['id']; ?>">
                <div class="form-group">
                    <label for="nome">Nome da Empresa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="index.php?route=empresas" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
// Incluir rodapé
include 'views/templates/footer.php';
?>

