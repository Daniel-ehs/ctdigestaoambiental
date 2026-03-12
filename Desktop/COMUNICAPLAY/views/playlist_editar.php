<?php

require_once __DIR__ . 
'/../controllers/PlaylistController.php';
require_once __DIR__ . 
'/../controllers/TelaController.php';
require_once __DIR__ . 
'/../controllers/MidiaController.php';

$playlistController = new PlaylistController();
$telaController = new TelaController();
$midiaController = new MidiaController();

// Busca todas as telas disponíveis
$telasResult = $telaController->getAll();
$telas = $telasResult["telas"] ?? [];

// Garante que $telas seja um array, mesmo que vazio
if (!is_array($telas)) {
    $telas = [];
}

// Pega o ID da playlist
$playlistId = $_GET['id'] ?? '';

if (empty($playlistId)) {
    header('Location: playlists.php');
    exit;
}

// Busca a playlist
$result = $playlistController->getById($playlistId);
if (!$result['success']) {
    header('Location: playlists.php');
    exit;
}

$playlist = $result['playlist'];

// Busca mídias já na playlist
$midiasNaPlaylistResult = $playlistController->getMidias($playlistId);
$midiasNaPlaylist = $midiasNaPlaylistResult['midias'] ?? [];
$midiasNaPlaylistIds = array_column($midiasNaPlaylist, 'id');

// Busca todas as mídias disponíveis (excluindo as que já estão na playlist)
$midiasDisponiveisResult = $midiaController->getAll(); // Pega todas as mídias
$todasMidias = $midiasDisponiveisResult['midias'] ?? [];

$midiasDisponiveis = array_filter($todasMidias, function($midia) use ($midiasNaPlaylistIds) {
    return !in_array($midia['id'], $midiasNaPlaylistIds);
});

$pageTitle = 'Editar Playlist';
$breadcrumb = [
    ['title' => 'Playlists', 'url' => 'playlists.php'],
    ['title' => 'Editar Playlist']
];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Editar Playlist: <?= sanitize($playlist['nome']) ?>
                </h5>
            </div>
            <div class="card-body">
                <form id="formEditarPlaylist">
                    <input type="hidden" name="id" value="<?= $playlist['id'] ?>">
                    
                    <div class="row">
                        <!-- Informações Básicas -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Informações Básicas
                            </h6>
                            
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome da Playlist *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= sanitize($playlist['nome']) ?>" required maxlength="200">
                                <div class="form-text">Máximo 200 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tela_id" class="form-label">Tela *</label>
                                <select class="form-select" id="tela_id" name="tela_id" required>
                                    <option value="">Selecione uma tela</option>
                                    <?php foreach ($telas as $tela): ?>
                                        <option value="<?= $tela['id'] ?>" 
                                                <?= $tela['id'] == $playlist['tela_id'] ? 'selected' : '' ?>>
                                            <?= sanitize($tela['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Agendamento -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-calendar-event me-2"></i>
                                Agendamento
                            </h6>
                            
                            <div class="mb-3">
                                <label for="data_inicio" class="form-label">Data e Hora de Início *</label>
                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($playlist['data_inicio'])) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="data_fim" class="form-label">Data e Hora de Fim *</label>
                                <input type="datetime-local" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($playlist['data_fim'])) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mídias -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-collection-play me-2"></i>
                                Mídias da Playlist
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Mídias Disponíveis</h6>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <div id="midiasDisponiveis">
                                                <?php if (empty($midiasDisponiveis)): ?>
                                                    <p class="text-muted text-center" id="placeholderMidiasDisponiveis">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Nenhuma mídia disponível para adicionar
                                                    </p>
                                                <?php else: ?>
                                                    <?php foreach ($midiasDisponiveis as $midia): ?>
                                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded" data-id="<?= $midia['id'] ?>" data-nome="<?= sanitize($midia['nome']) ?>" data-tipo="<?= sanitize($midia['tipo']) ?>" data-duracao="<?= $midia['duracao'] ?? 30 ?>">
                                                            <div>
                                                                <strong><?= sanitize($midia['nome']) ?></strong><br>
                                                                <small class="text-muted">
                                                                    <?= ucfirst($midia['tipo']) ?>
                                                                    <?php if ($midia['duracao']): ?>
                                                                        - <?= $midia['duracao'] ?>s
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarMidia(<?= $midia['id'] ?>)">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Mídias da Playlist</h6>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <div id="midiasPlaylist">
                                                <?php if (empty($midiasNaPlaylist)): ?>
                                                    <p class="text-muted text-center" id="placeholderMidiasPlaylist">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Adicione mídias à playlist
                                                    </p>
                                                <?php else: ?>
                                                    <?php foreach ($midiasNaPlaylist as $midia): ?>
                                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded" data-id="<?= $midia['id'] ?>">
                                                            <div>
                                                                <strong><?= sanitize($midia['nome']) ?></strong><br>
                                                                <small class="text-muted">
                                                                    <?= ucfirst($midia['tipo']) ?>
                                                                    - <?= $midia['tempo_exibicao'] ?? $midia['duracao'] ?? 30 ?>s
                                                                </small>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerMidia(<?= $midia['id'] ?>)">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="text-end">
                                <a href="playlists.php" class="btn btn-secondary me-2">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let midiasPlaylist = [];

// Inicializa com as mídias já existentes
<?php if (!empty($midiasNaPlaylist)): ?>
    <?php foreach ($midiasNaPlaylist as $midia): ?>
        midiasPlaylist.push({
            id: <?= $midia['id'] ?>,
            nome: "<?= sanitize($midia['nome']) ?>",
            tipo: "<?= sanitize($midia['tipo']) ?>",
            duracao: <?= $midia['tempo_exibicao'] ?? $midia['duracao'] ?? 30 ?>
        });
    <?php endforeach; ?>
<?php endif; ?>

// Adicionar mídia à playlist
function adicionarMidia(midiaId) {
    const midiasDisponiveisContainer = document.getElementById('midiasDisponiveis');
    const midiasPlaylistContainer = document.getElementById('midiasPlaylist');
    const midiaElement = midiasDisponiveisContainer.querySelector(`[data-id="${midiaId}"]`);
    
    if (!midiaElement) return;
    
    // Verifica se já foi adicionada
    if (midiasPlaylist.some(m => m.id === midiaId)) {
        alert('Esta mídia já foi adicionada à playlist.');
        return;
    }
    
    const nome = midiaElement.dataset.nome;
    const tipo = midiaElement.dataset.tipo;
    const duracao = midiaElement.dataset.duracao;

    // Adiciona ao array
    midiasPlaylist.push({ id: midiaId, nome: nome, tipo: tipo, duracao: duracao });
    
    // Clona o elemento para a playlist
    const clone = midiaElement.cloneNode(true);
    const button = clone.querySelector('button');
    button.className = 'btn btn-sm btn-outline-danger';
    button.innerHTML = '<i class="bi bi-trash"></i>';
    button.onclick = () => removerMidia(midiaId);
    
    // Atualiza a duração exibida no clone para a duração padrão (ou a da mídia se existir)
    const smallElement = clone.querySelector('small.text-muted');
    if (smallElement) {
        smallElement.innerHTML = `${ucfirst(tipo)} - ${duracao}s`;
    }

    // Adiciona à lista da playlist
    const placeholderPlaylist = document.getElementById('placeholderMidiasPlaylist');
    if (placeholderPlaylist) {
        placeholderPlaylist.remove();
    }
    midiasPlaylistContainer.appendChild(clone);
    
    // Remove o elemento original da lista de disponíveis
    midiaElement.remove();

    // Se a lista de disponíveis ficou vazia, mostra o placeholder
    if (midiasDisponiveisContainer.children.length === 0) {
        midiasDisponiveisContainer.innerHTML = '<p class="text-muted text-center" id="placeholderMidiasDisponiveis"><i class="bi bi-info-circle me-2"></i>Nenhuma mídia disponível para adicionar</p>';
    }
}

// Remover mídia da playlist
function removerMidia(midiaId) {
    const midiasDisponiveisContainer = document.getElementById('midiasDisponiveis');
    const midiasPlaylistContainer = document.getElementById('midiasPlaylist');
    const midiaElement = midiasPlaylistContainer.querySelector(`[data-id="${midiaId}"]`);

    if (!midiaElement) return;

    // Remove do array
    midiasPlaylist = midiasPlaylist.filter(m => m.id !== midiaId);
    
    // Remove da lista visual da playlist
    midiaElement.remove();
    
    // Se a lista da playlist ficou vazia, mostra mensagem
    if (midiasPlaylistContainer.children.length === 0) {
        midiasPlaylistContainer.innerHTML = '<p class="text-muted text-center" id="placeholderMidiasPlaylist"><i class="bi bi-info-circle me-2"></i>Adicione mídias à playlist</p>';
    }
    
    // Clona o elemento para a lista de disponíveis
    const clone = midiaElement.cloneNode(true);
    const button = clone.querySelector('button');
    button.className = 'btn btn-sm btn-outline-primary';
    button.innerHTML = '<i class="bi bi-plus"></i>';
    button.onclick = () => adicionarMidia(midiaId);

    // Adiciona à lista de disponíveis
    const placeholderDisponiveis = document.getElementById('placeholderMidiasDisponiveis');
    if (placeholderDisponiveis) {
        placeholderDisponiveis.remove();
    }
    midiasDisponiveisContainer.appendChild(clone);
}

// Submissão do formulário
document.getElementById('formEditarPlaylist').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Adiciona as mídias selecionadas
    midiasPlaylist.forEach((midia, index) => {
        formData.append(`midias[${index}][id]`, midia.id);
        formData.append(`midias[${index}][ordem]`, index + 1); // Ordem baseada na posição atual
        formData.append(`midias[${index}][tempo_exibicao]`, midia.duracao); // Usar a duração da mídia
    });
    
    // Validações básicas
    if (!formData.get('nome').trim()) {
        alert('Nome da playlist é obrigatório.');
        return;
    }
    
    if (!formData.get('tela_id')) {
        alert('Selecione uma tela.');
        return;
    }
    
    if (!formData.get('data_inicio')) {
        alert('Data de início é obrigatória.');
        return;
    }
    
    if (!formData.get('data_fim')) {
        alert('Data de fim é obrigatória.');
        return;
    }
    
    // Mostrar loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Salvando...';
    
    fetch('api/playlists/update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Playlist atualizada com sucesso!');
            window.location.href = 'playlists.php';
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar playlist.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Função auxiliar para capitalizar a primeira letra
function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/template.php';
?>

