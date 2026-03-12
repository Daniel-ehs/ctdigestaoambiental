<?php

require_once __DIR__ . '/../controllers/PlaylistController.php';

$playlistController = new PlaylistController();

// Parâmetros de filtro e paginação
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$telaId = $_GET['tela_id'] ?? null;

// Busca playlists
$result = $playlistController->getAll($page, $limit);
$playlists = $result['playlists'] ?? [];
$total = $result['total'] ?? 0;

// CORREÇÃO DO BUG: Adiciona campos calculados para cada playlist
// PROBLEMA ORIGINAL: foreach ($playlists as &$playlist) sem unset($playlist)
// SOLUÇÃO: Usar foreach sem referência
foreach ($playlists as $index => $playlist) {
    // Calcula status baseado nas datas
    $agora = date('Y-m-d H:i:s');
    if ($playlist['data_inicio'] <= $agora && $playlist['data_fim'] >= $agora) {
        $playlists[$index]['status'] = 'ativa';
    } elseif ($playlist['data_inicio'] > $agora) {
        $playlists[$index]['status'] = 'agendada';
    } else {
        $playlists[$index]['status'] = 'finalizada';
    }
    
    // Campos placeholder (serão implementados depois)
    $playlists[$index]['total_midias'] = 0;
    $playlists[$index]['duracao_total'] = 0;
    
    // Extrai horas das datas
    $playlists[$index]['hora_inicio'] = $playlist['data_inicio'] ? date('H:i', strtotime($playlist['data_inicio'])) : '';
    $playlists[$index]['hora_fim'] = $playlist['data_fim'] ? date('H:i', strtotime($playlist['data_fim'])) : '';
}
// CORREÇÃO: Limpar variáveis temporárias
unset($playlist, $index);

$user = SessionManager::getUser();

$pageTitle = 'Gerenciamento de Playlists';
$breadcrumb = [];

$pageActions = '
    <button class="btn btn-primary" onclick="window.location.href=\'playlist_criar.php\'">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Playlist
    </button>
';

// Função auxiliar apenas para status (formatDuration já existe em functions.php)
function getStatusColor($status) {
    switch ($status) {
        case 'ativa': return 'success';
        case 'agendada': return 'warning';
        case 'finalizada': return 'secondary';
        default: return 'secondary';
    }
}

ob_start();
?>

<style>
/* Estilos modernos para a página de playlists */
.modern-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

/* Cards de resumo com bordas coloridas à esquerda */
.summary-card {
    position: relative;
}

.summary-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    transition: all 0.3s ease;
}

.summary-card.total::before {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.summary-card.ativas::before {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.summary-card.agendadas::before {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
}

.summary-card:hover::before {
    width: 6px;
}

.modern-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1.5rem;
    color: white;
    border-radius: 16px 16px 0 0;
}

.modern-card-header h5 {
    font-weight: 600;
    margin: 0;
    font-size: 1.25rem;
}

.modern-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.modern-btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.modern-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.modern-btn:hover::before {
    left: 100%;
}

.modern-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modern-btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.modern-btn-outline-primary {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    border: none;
}

.modern-btn-outline-primary:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
}

.modern-btn-outline-secondary {
    background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
    color: white;
    border: none;
}

.modern-btn-outline-secondary:hover {
    background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(160, 174, 192, 0.4);
}

.modern-btn-outline-info {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    color: white;
    border: none;
}

.modern-btn-outline-info:hover {
    background: linear-gradient(135deg, #2f855a 0%, #276749 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
}

.modern-btn-outline-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    color: white;
    border: none;
}

.modern-btn-outline-danger:hover {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4);
}

.modern-empty-state {
    padding: 4rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 16px;
    margin: 2rem 0;
}

.modern-empty-state i {
    background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<div class="row mb-4 fade-in">
    <div class="col-4">
        <div class="modern-card summary-card total h-100">
            <div class="card-body text-center">
                <div class="h4 text-primary"><?= $total ?></div>
                <small class="text-muted">Total de Playlists</small>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="modern-card summary-card ativas h-100">
            <div class="card-body text-center">
                <div class="h4 text-success">
                    <?= count(array_filter($playlists, function($p) { return ($p['status'] ?? '') === 'ativa'; })) ?>
                </div>
                <small class="text-muted">Playlists Ativas</small>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="modern-card summary-card agendadas h-100">
            <div class="card-body text-center">
                <div class="h4 text-warning">
                    <?= count(array_filter($playlists, function($p) { return ($p['status'] ?? '') === 'agendada'; })) ?>
                </div>
                <small class="text-muted">Playlists Agendadas</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($playlists)): ?>
            <div class="modern-empty-state">
                <i class="bi bi-collection-play" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">Nenhuma playlist encontrada</h4>
                <p class="text-muted">Comece criando sua primeira playlist.</p>
                <button class="btn btn-primary" onclick="window.location.href='playlist_criar.php'">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nova Playlist
                </button>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($playlists as $playlist): ?>
                    <div class="col-md-6 col-lg-4 mb-4 fade-in">
                        <div class="card h-100 modern-card">
                            <div class="card-header modern-card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><?= htmlspecialchars($playlist['nome'] ?? 'Sem nome') ?></h6>
                                <span class="badge bg-<?= getStatusColor($playlist['status'] ?? 'finalizada') ?>">
                                    <?= ucfirst($playlist['status'] ?? 'Finalizada') ?>
                                </span>
                            </div>
                            
                            <div class="card-body">
                                <div class="mb-3">
                                    <?php if (!empty($playlist['data_inicio']) && !empty($playlist['data_fim'])): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-event me-2 text-muted"></i>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($playlist['data_inicio'])) ?> - 
                                                <?= date('d/m/Y', strtotime($playlist['data_fim'])) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($playlist['hora_inicio']) && !empty($playlist['hora_fim'])): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-clock me-2 text-muted"></i>
                                            <small class="text-muted">
                                                <?= $playlist['hora_inicio'] ?> - <?= $playlist['hora_fim'] ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($playlist['tela_nome'])): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-tv me-2 text-muted"></i>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($playlist['tela_nome']) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-sm modern-btn-outline-primary" onclick="visualizarPlaylist(<?= $playlist['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm modern-btn-outline-secondary" onclick="editarPlaylist(<?= $playlist['id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm modern-btn-outline-info" onclick="clonarPlaylist(<?= $playlist['id'] ?>)">
                                        <i class="bi bi-files"></i>
                                    </button>
                                    <button class="btn btn-sm modern-btn-outline-danger" onclick="excluirPlaylist(<?= $playlist['id'] ?>, '<?= addslashes($playlist['nome'] ?? 'Playlist') ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($total > $limit): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Navegação de páginas">
                <ul class="pagination justify-content-center">
                    <?php
                    $totalPages = ceil($total / $limit);
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">
                                Próximo <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>

<!-- Modal para Confirmar Exclusão -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalConfirmarExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarExclusaoLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a playlist <strong id="nomePlaylistExcluir"></strong>?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Visualizar Playlist -->
<div class="modal fade" id="modalVisualizarPlaylist" tabindex="-1" aria-labelledby="modalVisualizarPlaylistLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizarPlaylistLabel">
                    <i class="bi bi-eye me-2"></i>
                    Detalhes da Playlist
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="playlistDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2">Carregando detalhes...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnEditarPlaylistModal" onclick="editarPlaylistFromModal()">
                    <i class="bi bi-pencil me-2"></i>
                    Editar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>

<script>
// Variável global para armazenar o ID da playlist sendo visualizada
let playlistAtualId = null;

// Função para visualizar playlist
function visualizarPlaylist(id) {
    playlistAtualId = id;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalVisualizarPlaylist'));
    modal.show();
    
    // Buscar dados da playlist via AJAX
    fetch('/public/api/playlists/get.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPlaylistDetails(data.playlist);
            } else {
                document.getElementById('playlistDetailsContent').innerHTML = 
                    '<div class="alert alert-danger">Erro ao carregar playlist: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            document.getElementById('playlistDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Erro ao carregar playlist</div>';
        });
}

// Função para renderizar os detalhes da playlist
function renderPlaylistDetails(playlist) {
    const midias = playlist.midias || [];
    
    let html = `
        <div class="row mb-3">
            <div class="col-4"><strong>Nome:</strong></div>
            <div class="col-8">${playlist.nome || 'Não informado'}</div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><strong>Tela:</strong></div>
            <div class="col-8">${playlist.tela_nome || 'Não informado'}</div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><strong>Status:</strong></div>
            <div class="col-8">
                <span class="badge bg-${getStatusColorJS(playlist.status)}">
                    ${playlist.status ? playlist.status.charAt(0).toUpperCase() + playlist.status.slice(1) : 'Finalizada'}
                </span>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><strong>Data Início:</strong></div>
            <div class="col-8">${formatDateJS(playlist.data_inicio) || 'Não informado'}</div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><strong>Data Fim:</strong></div>
            <div class="col-8">${formatDateJS(playlist.data_fim) || 'Não informado'}</div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><strong>Criado em:</strong></div>
            <div class="col-8">${formatDateJS(playlist.data_criacao) || 'Não informado'}</div>
        </div>
        
        <hr>
        <h6><i class="bi bi-collection-play me-2"></i>Mídias da Playlist (${midias.length})</h6>
    `;
    
    if (midias.length > 0) {
        html += '<div class="list-group">';
        midias.forEach((midia, index) => {
            html += `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${index + 1}. ${midia.nome || 'Sem nome'}</h6>
                        <small class="text-muted">${midia.tempo_exibicao || 0}s</small>
                    </div>
                    <small class="text-muted">Tipo: ${midia.tipo || 'Não informado'}</small>
                </div>
            `;
        });
        html += '</div>';
    } else {
        html += '<div class="alert alert-info">Nenhuma mídia associada a esta playlist.</div>';
    }
    
    document.getElementById('playlistDetailsContent').innerHTML = html;
}

// Função auxiliar para obter cor do status em JavaScript
function getStatusColorJS(status) {
    switch (status) {
        case 'ativa': return 'success';
        case 'agendada': return 'warning';
        case 'finalizada': return 'secondary';
        default: return 'secondary';
    }
}

// Função auxiliar para formatar data em JavaScript
function formatDateJS(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    } catch (e) {
        return dateString;
    }
}

// Função para editar playlist a partir do modal
function editarPlaylistFromModal() {
    if (playlistAtualId) {
        editarPlaylist(playlistAtualId);
    }
}

// Função para editar playlist
function editarPlaylist(id) {
    window.location.href = 'playlist_editar.php?id=' + id;
}

// Função para clonar playlist
function clonarPlaylist(id) {
    // Mostrar indicador de carregamento
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
    button.disabled = true;
    
    // Usar FormData em vez de JSON
    const formData = new FormData();
    formData.append('id', id);
    
    // Fazer requisição AJAX para clonar
    fetch('/public/api/playlists/clone.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensagem de sucesso
            showToast('Playlist clonada com sucesso!', 'success');
            // Recarregar página após um breve delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('Erro ao clonar playlist: ' + data.message, 'error');
            // Restaurar botão
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao clonar playlist', 'error');
        // Restaurar botão
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

// Função para excluir playlist
function excluirPlaylist(id, nome) {
    document.getElementById('nomePlaylistExcluir').textContent = nome;
    document.getElementById('btnConfirmarExclusao').onclick = function() {
        
        // Usar FormData em vez de JSON
        const formData = new FormData();
        formData.append('id', id);
        
        // Fazer requisição AJAX para excluir
        fetch('/public/api/playlists/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Playlist excluída com sucesso!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Erro ao excluir playlist: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('Erro ao excluir playlist', 'error');
        });
        
        // Fechar modal
        bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao')).hide();
    };
    
    // Mostrar modal
    new bootstrap.Modal(document.getElementById('modalConfirmarExclusao')).show();
}

// Função para mostrar toast de notificação
function showToast(message, type = 'info') {
    // Criar elemento de toast se não existir
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Criar toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
    toast.style.cssText = `
        min-width: 300px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Remover automaticamente após 5 segundos
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}
</script>
