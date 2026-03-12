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

// Adiciona status calculado para cada playlist
foreach ($playlists as &$playlist) {
    $agora = date('Y-m-d H:i:s');
    if ($playlist['data_inicio'] <= $agora && $playlist['data_fim'] >= $agora) {
        $playlist['status'] = 'ativa';
    } elseif ($playlist['data_inicio'] > $agora) {
        $playlist['status'] = 'agendada';
    } else {
        $playlist['status'] = 'finalizada';
    }
    
    // Calcula total de mídias (placeholder)
    $playlist['total_midias'] = 0;
    $playlist['duracao_total'] = 0;
}

$user = SessionManager::getUser();

$pageTitle = 'Gerenciamento de Playlists';
$breadcrumb = [
    ['title' => 'Playlists']
];

$pageActions = '
    <a href="playlist_criar.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Playlist
    </a>
';

// Funções auxiliares
function getStatusColor($status) {
    switch ($status) {
        case 'ativa': return 'success';
        case 'agendada': return 'warning';
        case 'finalizada': return 'secondary';
        default: return 'secondary';
    }
}

function formatDuration($seconds) {
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return floor($seconds / 60) . 'm';
    return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
}

ob_start();
?>

<!-- Resumo -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h4 text-primary"><?= count($playlists) ?></div>
                        <small class="text-muted">Playlists</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-success">
                            <?= count(array_filter($playlists, function($p) { return $p['status'] === 'ativa'; })) ?>
                        </div>
                        <small class="text-muted">Ativas</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-warning">
                            <?= count(array_filter($playlists, function($p) { return $p['status'] === 'agendada'; })) ?>
                        </div>
                        <small class="text-muted">Agendadas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Playlists -->
<div class="row">
    <div class="col-12">
        <?php if (empty($playlists)): ?>
            <div class="text-center py-5">
                <i class="bi bi-collection-play text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">Nenhuma playlist encontrada</h4>
                <p class="text-muted">Comece criando sua primeira playlist.</p>
                <a href="playlist_criar.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nova Playlist
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($playlists as $playlist): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card h-100 playlist-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-collection-play me-2"></i>
                                    <?= sanitize($playlist['nome']) ?>
                                </h6>
                                <span class="badge bg-<?= getStatusColor($playlist['status']) ?>">
                                    <?= ucfirst($playlist['status']) ?>
                                </span>
                            </div>
                            
                            <div class="card-body">
                                <div class="playlist-info mb-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="h6 text-primary"><?= $playlist['total_midias'] ?></div>
                                            <small class="text-muted">Mídias</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h6 text-info"><?= formatDuration($playlist['duracao_total']) ?></div>
                                            <small class="text-muted">Duração</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h6 text-success">
                                                <?= $playlist['tela_nome'] ? '✓' : '✗' ?>
                                            </div>
                                            <small class="text-muted">Tela</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="playlist-schedule">
                                    <?php if ($playlist['data_inicio'] && $playlist['data_fim']): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-event me-2 text-muted"></i>
                                            <small class="text-muted">
                                                <?= formatDate($playlist['data_inicio']) ?> - 
                                                <?= formatDate($playlist['data_fim']) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($playlist['tela_nome']): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-tv me-2 text-muted"></i>
                                            <small class="text-muted">
                                                <?= sanitize($playlist['tela_nome']) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="visualizarPlaylist(<?= $playlist['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editarPlaylist(<?= $playlist['id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="clonarPlaylist(<?= $playlist['id'] ?>)">
                                        <i class="bi bi-files"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirPlaylist(<?= $playlist['id'] ?>, '<?= addslashes($playlist['nome']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginação -->
            <?php if ($total > $limit): ?>
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination justify-content-center">
                        <!-- Implementar paginação aqui -->
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para visualizar playlist -->
<div class="modal fade" id="modalVisualizarPlaylist" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Playlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conteudoPlaylist">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function visualizarPlaylist(id) {
    // Carregar detalhes da playlist via AJAX
    fetch(`api/playlists/get.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const playlist = data.playlist;
                let content = `
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h5>${playlist.nome}</h5>
                            <p class="text-muted">${playlist.descricao || 'Sem descrição'}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-${getStatusColor(playlist.status)} fs-6">
                                ${playlist.status ? playlist.status.charAt(0).toUpperCase() + playlist.status.slice(1) : 'N/A'}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Tela:</strong> ${playlist.tela_nome || 'N/A'}<br>
                            <strong>Período:</strong> ${formatDateTime(playlist.data_inicio)} - ${formatDateTime(playlist.data_fim)}
                        </div>
                        <div class="col-md-6">
                            <strong>Criado por:</strong> ${playlist.usuario_nome || 'N/A'}<br>
                            <strong>Data de criação:</strong> ${formatDateTime(playlist.data_criacao)}
                        </div>
                    </div>
                `;
                
                if (playlist.midias && playlist.midias.length > 0) {
                    content += `
                        <h6>Mídias da Playlist:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Ordem</th>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Duração</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    playlist.midias.forEach(midia => {
                        content += `
                            <tr>
                                <td>${midia.ordem}</td>
                                <td>${midia.nome}</td>
                                <td>${midia.tipo}</td>
                                <td>${midia.duracao_personalizada || midia.duracao || 'N/A'}s</td>
                            </tr>
                        `;
                    });
                    
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted">Nenhuma mídia adicionada a esta playlist.</p>';
                }
                
                document.getElementById('conteudoPlaylist').innerHTML = content;
                new bootstrap.Modal(document.getElementById('modalVisualizarPlaylist')).show();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar playlist.');
        });
}

function editarPlaylist(id) {
    window.location.href = `playlist_editar.php?id=${id}`;
}

function clonarPlaylist(id) {
    if (confirm('Deseja criar uma cópia desta playlist?')) {
        fetch(`api/playlists/clone.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Playlist clonada com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao clonar playlist.');
        });
    }
}

function excluirPlaylist(id, nome) {
    if (confirm(`Tem certeza que deseja excluir a playlist "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        fetch(`api/playlists/delete.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Playlist excluída com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir playlist.');
        });
    }
}

function getStatusColor(status) {
    switch (status) {
        case 'ativa': return 'success';
        case 'agendada': return 'warning';
        case 'finalizada': return 'secondary';
        default: return 'secondary';
    }
}

function formatDateTime(dateTime) {
    if (!dateTime) return 'N/A';
    return new Date(dateTime).toLocaleString('pt-BR');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>

