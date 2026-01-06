<?php
/**
 * View para formulário de relatório semanal
 */

include 'views/templates/header.php';

if (!isset($_SESSION['user_id'])) {
    redirect('index.php?route=login');
}
?>

<!-- CSS -->
<style>
/* Estilos permanecem os mesmos */
body {
    background: #f5f6f5;
    font-family: 'Poppins', sans-serif;
    color: #333;
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
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1), 0 0 20px rgba(40,167,69,0.2);
    margin: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: fadeIn 0.5s ease-out;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    background: linear-gradient(90deg, #28a745, #52c41a);
    color: #fff;
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
    text-align: center;
}

.card-body {
    padding: 2rem;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.form-select, .form-control {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-select:focus, .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 8px rgba(40,167,69,0.3);
    outline: none;
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.95rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #52c41a);
    color: #fff;
    border: none;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #fff;
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: #fff;
    border: none;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
    background-color: #f8f9fa;
    font-weight: 600;
}

/* Estilo para reduzir a fonte da tabela de relatórios */
.table-relatorios-emitidos th,
.table-relatorios-emitidos td {
    font-size: 0.8rem; /* Fonte reduzida */
}
</style>

<canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

<div class="top-bar">
    <h1 class="h2 m-0">Relatório Semanal</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Gerar Relatório Semanal</h6>
    </div>
    <div class="card-body">
        <?php include 'views/components/flash_messages.php'; ?>
        <form method="POST" action="index.php?route=relatorios&action=generate&type=semanal">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="empresa_id" class="form-label">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">Todas as empresas</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="semana" class="form-label">Semana</label>
                    <select class="form-select" id="semana" name="semana" required>
                        <option value="">Selecione uma semana</option>
                        <?php foreach ($semanas as $semana): ?>
                            
                            <option value="<?= $semana['ano'] ?>-<?= $semana['numero'] ?>">
                                Semana <?= $semana['numero'] ?> (<?= $semana['inicio'] ?> a <?= $semana['fim'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> Gerar Relatório
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Relatórios Emitidos</h6>
    </div>
    <div class="card-body">
        <?php
        if (isset($relatoriosEmitidos) && !empty($relatoriosEmitidos)) {
            $isAdmin = isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-hover table-relatorios-emitidos">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Nome do Relatório</th>';
            echo '<th>Empresa</th>';
            echo '<th>Data de Geração</th>';
            echo '<th>Tamanho (KB)</th>';
            echo '<th>Ações</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($relatoriosEmitidos as $relatorio) {
                $pdfFile = 'uploads/pdfs/' . $relatorio['file'];
                $fileName = $relatorio['file'];
                $fileDate = date('d/m/Y H:i:s', $relatorio['timestamp']);
                $fileSize = round(filesize($pdfFile) / 1024, 2);
                $displayName = "Relatório Semanal - Semana {$relatorio['semana']}/{$relatorio['ano']}";
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($displayName) . '</td>';
                echo '<td>' . htmlspecialchars($relatorio['empresa_nome']) . '</td>';
                echo '<td>' . $fileDate . '</td>';
                echo '<td>' . $fileSize . '</td>';
                echo '<td>';
                echo '<a href="' . htmlspecialchars($pdfFile) . '" class="btn btn-sm btn-primary me-2" download title="Baixar"><i class="fas fa-download"></i></a>';
                echo '<a href="' . htmlspecialchars($pdfFile) . '" class="btn btn-sm btn-secondary me-2" target="_blank" title="Visualizar"><i class="fas fa-eye"></i></a>';
                
                if ($isAdmin) {
                    $deleteUrl = 'index.php?route=relatorios&action=deleteRelatorio&file=' . urlencode($fileName);
                    echo '<button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-delete-url="' . htmlspecialchars($deleteUrl) . '" title="Excluir"><i class="fas fa-trash"></i></button>';
                }
                
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">';
            echo '<i class="fas fa-info-circle me-2"></i> Nenhum relatório foi gerado ainda.';
            echo '</div>';
        }
        ?>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja excluir este relatório? <strong>Esta operação não pode ser desfeita.</strong>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Excluir</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    particlesJS('particles', { /* ... (particles config) ... */ });
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    if (confirmDeleteModal) {
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const deleteUrl = button.getAttribute('data-delete-url');
            const confirmBtn = confirmDeleteModal.querySelector('#confirmDeleteBtn');
            confirmBtn.setAttribute('href', deleteUrl);
        });
    }
});
</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<?php include 'views/templates/footer.php'; ?>
