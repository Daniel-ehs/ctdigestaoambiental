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
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1), 0 0 20px rgba(40, 167, 69, 0.2);
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

    .form-select,
    .form-control {
        border-radius: 10px;
        border: 1px solid #ced4da;
        padding: 0.75rem;
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.3);
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
        font-size: 0.8rem;
        /* Fonte reduzida */
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
        <form method="POST" action="index.php?route=relatorios&action=generate&type=semanal" id="generateReportForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="empresa_id" class="form-label">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">Todas as empresas</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa["id"] ?>"><?= htmlspecialchars($empresa["nome"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ano" class="form-label">Ano</label>
                    <select class="form-select" id="ano" name="ano" required>
                        <?php
                        $currentYear = date('Y');
                        foreach ($anosDisponiveis as $anoDisp):
                            ?>
                            <option value="<?= $anoDisp ?>" <?= $anoDisp == $currentYear ? 'selected' : '' ?>><?= $anoDisp ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="semana" class="form-label">Semana</label>
                    <select class="form-select" id="semana" name="semana" required>
                        <option value="">Selecione...</option>
                        <?php for ($i = 1; $i <= 53; $i++): ?>
                            <option value="<?= $i ?>">Semana <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <div id="periodo-display" class="alert alert-info" style="display:none; margin-bottom: 0;">
                        <i class="fas fa-calendar-alt me-2"></i> <span id="periodo-texto">Selecione o ano e a semana
                            para ver o período.</span>
                    </div>
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

<!-- Campo de Busca -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <input type="text" id="searchQuery" class="form-control"
                    placeholder="Buscar por nome do relatório ou empresa..."
                    value="<?= htmlspecialchars($pagination['searchQuery'] ?? '') ?>">
            </div>
        </div>
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
            echo '<th>Ações</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($relatoriosEmitidos as $relatorio) {
                $fileUrl = $relatorio['file'];
                $fileDate = date('d/m/Y H:i:s', $relatorio['timestamp']);
                $displayName = "Relatório Semanal - Semana {$relatorio['semana']}/{$relatorio['ano']}";

                // Verifica se é um relatório novo (MinIO) ou antigo (local)
                $isMinioReport = strpos($fileUrl, 'http') === 0;

                echo '<tr>';
                echo '<td>' . htmlspecialchars($displayName) . '</td>';
                echo '<td>' . htmlspecialchars($relatorio['empresa_nome']) . '</td>';
                echo '<td>' . $fileDate . '</td>';
                echo '<td>';

                if ($isMinioReport) {
                    // Relatório novo: link para o URL do MinIO
                    echo '<a href="' . htmlspecialchars($fileUrl) . '" class="btn btn-sm btn-secondary me-2" target="_blank" title="Visualizar"><i class="fas fa-eye"></i></a>';
                } else {
                    // Relatório antigo: botão que aciona o modal de indisponibilidade
                    echo '<button type="button" class="btn btn-sm btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#unavailableReportModal" title="Visualizar"><i class="fas fa-eye"></i></button>';
                }

                if ($isAdmin) {
                    // O parâmetro para exclusão deve ser o nome do arquivo ou o URL completo, dependendo do tipo
                    $fileIdentifier = $relatorio['file'];
                    $deleteUrl = 'index.php?route=relatorios&action=deleteRelatorio&file=' . urlencode($fileIdentifier);
                    echo '<button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-delete-url="' . htmlspecialchars($deleteUrl) . '" title="Excluir"><i class="fas fa-trash"></i></button>';
                }

                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            // Controles de Paginação
            if ($pagination['totalPages'] > 1) {
                echo '<nav aria-label="Page navigation">';
                echo '<ul class="pagination justify-content-center mt-4">';

                // Botão Anterior
                $prevDisabled = ($pagination['currentPage'] <= 1) ? 'disabled' : '';
                $prevPage = $pagination['currentPage'] - 1;
                $prevLink = 'index.php?route=relatorios&page=' . $prevPage . (!empty($pagination['searchQuery']) ? '&search=' . urlencode($pagination['searchQuery']) : '');
                echo '<li class="page-item ' . $prevDisabled . '">' . '<a class="page-link" href="' . $prevLink . '">Anterior</a></li>';

                // Links para páginas
                for ($i = 1; $i <= $pagination['totalPages']; $i++) {
                    $active = ($i == $pagination['currentPage']) ? 'active' : '';
                    $pageLink = 'index.php?route=relatorios&page=' . $i . (!empty($pagination['searchQuery']) ? '&search=' . urlencode($pagination['searchQuery']) : '');
                    echo '<li class="page-item ' . $active . '">' . '<a class="page-link" href="' . $pageLink . '">' . $i . '</a></li>';
                }

                // Botão Próximo
                $nextDisabled = ($pagination['currentPage'] >= $pagination['totalPages']) ? 'disabled' : '';
                $nextPage = $pagination['currentPage'] + 1;
                $nextLink = 'index.php?route=relatorios&page=' . $nextPage . (!empty($pagination['searchQuery']) ? '&search=' . urlencode($pagination['searchQuery']) : '');
                echo '<li class="page-item ' . $nextDisabled . '">' . '<a class="page-link" href="' . $nextLink . '">Próximo</a></li>';

                echo '</ul>';
                echo '</nav>';
            }

        } else {
            echo '<div class="alert alert-info">';
            echo '<i class="fas fa-info-circle me-2"></i> Nenhum relatório foi gerado ainda.';
            echo '</div>';
        }
        ?>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
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
                <a id="confirmDeleteButton" class="btn btn-danger" href="#">Excluir</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Relatório Indisponível -->
<div class="modal fade" id="unavailableReportModal" tabindex="-1" aria-labelledby="unavailableReportModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unavailableReportModalLabel">Relatório Indisponível</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Este relatório não está disponível para visualização online, pois foi gerado antes da migração para o
                novo sistema de armazenamento. Apenas relatórios gerados após a atualização podem ser abertos
                diretamente.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var deleteUrl = button.getAttribute('data-delete-url');
            var confirmButton = confirmDeleteModal.querySelector('#confirmDeleteButton');
            confirmButton.href = deleteUrl;
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        particlesJS('particles', {
            particles: {
                number: { value: 50, density: { enable: true, value_area: 800 } },
                color: { value: '#28a745' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#52c41a', opacity: 0.4, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: true }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true },
                modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });
    });
</script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<?php include 'views/templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const generateForm = document.querySelector('form[action="index.php?route=relatorios&action=generate&type=semanal"]');
        if (generateForm) {
            generateForm.addEventListener('submit', function (e) {
                e.preventDefault(); // Previne a submissão padrão do formulário

                const formData = new FormData(generateForm);
                const actionUrl = generateForm.getAttribute('action');

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.pdfUrl) {
                            // Abre o PDF em uma nova aba
                            window.open(data.pdfUrl, '_blank');
                            // Recarrega a página atual para atualizar a lista de relatórios
                            window.location.reload();
                        } else {
                            // Exibe mensagem de erro se houver
                            alert(data.message || 'Erro ao gerar relatório.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        alert('Ocorreu um erro ao gerar o relatório. Tente novamente.');
                    });
            });
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchQueryInput = document.getElementById("searchQuery");
        if (searchQueryInput) {
            searchQueryInput.value = new URLSearchParams(window.location.search).get("search") || "";
            searchQueryInput.addEventListener("keyup", function (e) {
                if (e.key === "Enter") {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set("search", this.value);
                    currentUrl.searchParams.delete("page"); // Resetar paginação ao buscar
                    window.location.href = currentUrl.toString();
                }
            });
        }

        // --- JAVASCRIPT PARA CÁLCULO DE DATA DA SEMANA ---
        const anoSelect = document.getElementById('ano');
        const semanaSelect = document.getElementById('semana');
        const displayDiv = document.getElementById('periodo-display');
        const displayText = document.getElementById('periodo-texto');

        function updatePeriodo() {
            const ano = parseInt(anoSelect.value);
            const semana = parseInt(semanaSelect.value);

            if (!ano || !semana) {
                displayDiv.style.display = 'none';
                return;
            }

            // Função para obter a data de início da semana ISO
            function getDateOfISOWeek(w, y) {
                var simple = new Date(y, 0, 1 + (w - 1) * 7);
                var dow = simple.getDay();
                var ISOweekStart = simple;
                if (dow <= 4)
                    ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
                else
                    ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
                return ISOweekStart;
            }

            const startDate = getDateOfISOWeek(semana, ano);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);

            // Formatação pt-BR
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            const startStr = startDate.toLocaleDateString('pt-BR', options);
            const endStr = endDate.toLocaleDateString('pt-BR', options);

            displayText.innerHTML = `Período: <strong>${startStr}</strong> a <strong>${endStr}</strong>`;
            displayDiv.style.display = 'block';
        }

        anoSelect.addEventListener('change', updatePeriodo);
        semanaSelect.addEventListener('change', updatePeriodo);
    });
</script>