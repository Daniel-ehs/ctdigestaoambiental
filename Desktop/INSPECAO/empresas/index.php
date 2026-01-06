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

// Simulação de dados, caso a variável $empresas não venha do controller
// $empresas = $empresas ?? []; 
?>

<style>
/* =================================================================== */
/* ESTILOS UNIFICADOS (BASEADOS NA PÁGINA DE INSPEÇÕES) E ADAPTADOS */
/* =================================================================== */
body {
    background: #f5f6f5;
    font-family: 'Poppins', sans-serif;
    color: #333333;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.top-bar {
    background: #f5f6f5;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 10;
}

.card {
    background: #ffffff;
    border-radius: 16px;
    /* Cor do box-shadow adaptada para o azul */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1), 0 0 20px rgba(0, 123, 255, 0.2);
    margin: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    /* Cor do gradiente adaptada para o azul */
    background: linear-gradient(90deg, #007bff, #0056b3);
    color: white;
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-body {
    padding: 2.5rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.table th,
.table td {
    padding: 0.75rem 1rem; /* Padding ajustado para ficar mais elegante */
    vertical-align: middle;
    border: 1px solid #dee2e6;
    transition: background-color 0.3s ease;
}

.table th {
    background: #f0f4f8; /* Fundo do header da tabela adaptado para azul claro */
    color: #007bff; /* Cor do texto adaptada para o azul */
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.table tbody tr {
    background: #ffffff;
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: #f0f4f8; /* Fundo do hover adaptado */
    transform: scale(1.01);
}

.table tbody td {
    color: #333333;
    font-size: 0.9rem; /* Fonte um pouco maior que a de inspeções para melhor leitura */
}

.btn {
    position: relative;
    border-radius: 10px;
    /* Sombra do botão adaptada para o azul */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1), 0 0 15px rgba(0, 123, 255, 0.3);
    border: none;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    /* Gradiente do botão primário adaptado para o azul */
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn:hover {
    filter: brightness(1.1);
    transform: translateY(-2px);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: white;
}

.btn-group {
    display: flex;
    gap: 5px; /* Aumentei um pouco o gap para melhor espaçamento */
    flex-wrap: nowrap;
    justify-content: center;
}

/* Estilo para os botões de ação na tabela (agora como ícones) */
.btn-group .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px; /* Tamanho ajustado */
    height: 30px; /* Tamanho ajustado */
    border-radius: 8px; /* Borda mais arredondada */
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
    font-size: 14px; /* Tamanho do ícone */
    line-height: 1;
    padding: 0;
}

.btn-group .btn:not(.disabled):hover {
    transform: scale(1.1);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
    filter: brightness(1.15);
}

.actions-column {
    min-width: 120px; /* Largura mínima ajustada */
    text-align: center;
}

/* =================================================================== */
/* CSS para transformar o botão "Nova Empresa" em um ícone no mobile   */
/* IDÊNTICO AO DA PÁGINA DE INSPEÇÕES                                  */
/* =================================================================== */

@media (max-width: 767px) {
    .top-bar {
        padding: 1rem;
    }

    /* 1. Esconde o TEXTO do botão, mas não o ícone (o truque principal) */
    .top-bar a.btn {
        font-size: 0; /* Isso esconde o texto "Nova Empresa" */
        width: auto;
        height: auto;
        border-radius: 10px;
        padding: 0.75rem;
    }

    /* 2. Devolve o tamanho da fonte APENAS para o ícone */
    .top-bar a.btn i {
        font-size: 1.2rem;
        margin: 0;
    }

    .card {
        margin: 1rem;
    }

    .card-body {
        padding: 1.5rem 1rem;
    }
    
    .table th, .table td {
        padding: 0.75rem;
        font-size: 0.8rem;
    }

    /* Esconde o texto dos botões de ação na tabela, deixando só os ícones */
    .btn-group .btn .btn-text-inner {
        display: none;
    }
}
</style>

<div class="top-bar">
    <h1 class="h2 m-0" style="color: #333333;">Gerenciamento de Empresas</h1>
    <a href="index.php?route=empresas&action=create" class="btn btn-primary" title="Nova Empresa">
        <i class="fas fa-plus-circle"></i> Nova Empresa
    </a>
</div>

<div class="container-fluid pt-4">

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert" style="margin: 0 2rem 1rem 2rem;">
            <?php echo $_SESSION['flash_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold">Lista de Empresas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="companyTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Data de Criação</th>
                            <th>Última Atualização</th>
                            <th class="actions-column">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($empresas)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhuma empresa cadastrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($empresas as $empresa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($empresa['id']); ?></td>
                                    <td><?php echo htmlspecialchars($empresa['nome']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($empresa['created_at'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($empresa['updated_at'])); ?></td>
                                    <td class="actions-column">
                                        <div class="btn-group">
                                            <a href="index.php?route=empresas&action=edit&id=<?php echo $empresa['id']; ?>" class="btn btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-delete" 
                                                    data-id="<?php echo $empresa['id']; ?>" 
                                                    data-nome="<?php echo htmlspecialchars($empresa['nome']); ?>" 
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a empresa <strong id="empresaNome"></strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" action="index.php?route=empresas&action=delete">
                    <input type="hidden" name="id" id="empresaId">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Script para o modal de exclusão (sem alterações, já é moderno e funcional)
document.addEventListener('DOMContentLoaded', function () {
    const deleteModalElement = document.getElementById('deleteModal');
    
    // Certifique-se de que o Bootstrap 5 JS está carregado
    if (typeof bootstrap !== 'undefined') {
        const deleteModal = new bootstrap.Modal(deleteModalElement);
        const empresaNomeElement = document.getElementById('empresaNome');
        const empresaIdInput = document.getElementById('empresaId');

        // Adiciona evento de clique a todos os botões de exclusão
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nome = this.getAttribute('data-nome');

                // Preenche os dados no modal
                if(empresaNomeElement) empresaNomeElement.textContent = nome;
                if(empresaIdInput) empresaIdInput.value = id;
                
                // Exibe o modal
                deleteModal.show();
            });
        });
    } else {
        console.error('Bootstrap 5 JavaScript não foi carregado. O modal de exclusão não funcionará.');
    }
});
</script>

<?php
// Incluir rodapé
include 'views/templates/footer.php';
?>