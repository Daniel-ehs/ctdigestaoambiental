<?php include 'views/templates/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Alterar Senha</h5>
                </div>
                <div class="card-body">
                    <?php if ($flashMessage = getFlashMessage()): ?>
                    <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show">
                        <?php echo $flashMessage['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="index.php?route=auth&action=alterarSenha">
                        <div class="mb-3">
                            <label for="senha_atual" class="form-label">Senha Atual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                <span class="input-group-text" onclick="togglePassword('senha_atual')"><i id="eye-icon-atual" class="fas fa-eye"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nova_senha" class="form-label">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                                <span class="input-group-text" onclick="togglePassword('nova_senha')"><i id="eye-icon-nova" class="fas fa-eye"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                                <span class="input-group-text" onclick="togglePassword('confirmar_senha')"><i id="eye-icon-confirmar" class="fas fa-eye"></i></span>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Nova Senha
                            </button>
                            <a href="index.php?route=dashboard" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    var eyeIcon = document.getElementById("eye-icon-" + inputId.split('_').pop());
    
    if (input.type === "password") {
        input.type = "text";
        eyeIcon.className = "fas fa-eye-slash";
    } else {
        input.type = "password";
        eyeIcon.className = "fas fa-eye";
    }
}
</script>

<?php include 'views/templates/footer.php'; ?>
