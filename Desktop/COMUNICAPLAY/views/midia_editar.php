<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Midia.php';

// Verifica se está logado
SessionManager::requireLogin();

$midiaModel = new Midia();

// Pega o ID da mídia
$midiaId = $_GET['id'] ?? '';

if (empty($midiaId)) {
    header('Location: midias.php');
    exit;
}

// Busca a mídia
$midia = $midiaModel->getById($midiaId);
if (!$midia) {
    header('Location: midias.php');
    exit;
}

// Função auxiliar para sanitizar dados
function sanitizeData($data) {
    if (is_null($data)) {
        return '';
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Função auxiliar para formatar duração
function formatDurationSafe($seconds) {
    if (empty($seconds) || !is_numeric($seconds)) {
        return 'N/A';
    }
    
    $seconds = (int)$seconds;
    if ($seconds < 60) {
        return $seconds . 's';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return $minutes . 'm ' . $remainingSeconds . 's';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . 'h ' . $minutes . 'm';
    }
}

// Função auxiliar para formatar tamanho de arquivo
function formatFileSizeSafe($bytes) {
    if (empty($bytes) || !is_numeric($bytes)) {
        return 'N/A';
    }
    
    $bytes = (int)$bytes;
    if ($bytes < 1024) {
        return $bytes . ' B';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2) . ' KB';
    } elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . ' MB';
    } else {
        return round($bytes / 1073741824, 2) . ' GB';
    }
}

// Função auxiliar para formatar data
function formatDateSafe($date) {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format('d/m/Y H:i');
    } catch (Exception $e) {
        return 'N/A';
    }
}

// Processa o formulário se foi enviado
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $url_externa = trim($_POST['url_externa'] ?? '');
    $tempo_exibicao = (int)($_POST['tempo_exibicao'] ?? 0);
    
    // Validações básicas
    if (empty($nome)) {
        $message = 'O nome da mídia é obrigatório.';
        $messageType = 'danger';
    } else {
        try {
            // Usar apenas o método update existente
            $resultado1 = $midiaModel->update($midiaId, $nome, $tempo_exibicao);
            
            // Atualizar apenas URL externa (sem data_atualizacao que não existe)
            $db = Database::getInstance();
            $stmt = $db->getConnection()->prepare("
                UPDATE midias 
                SET url_externa = ?
                WHERE id = ?
            ");
            $resultado2 = $stmt->execute([$url_externa, $midiaId]);
            
            if ($resultado1 || $resultado2) {
                $message = 'Mídia atualizada com sucesso!';
                $messageType = 'success';
                // Recarrega os dados da mídia
                $midia = $midiaModel->getById($midiaId);
            } else {
                $message = 'Nenhuma alteração foi feita.';
                $messageType = 'info';
            }
        } catch (Exception $e) {
            $message = 'Erro ao atualizar a mídia: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

$pageTitle = 'Editar Mídia';

ob_start();
?>

<style>
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
}

.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
}

.form-text {
    color: #718096;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.botoes-acao {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Editar Mídia: <?= sanitizeData($midia['nome'] ?? 'Mídia sem nome') ?>
                </h5>
            </div>
            <div class="card-body">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'info' ? 'info-circle' : 'exclamation-triangle') ?> me-2"></i>
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="formEditarMidia">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-4">
                                <i class="bi bi-pencil-square me-2"></i>
                                Informações Editáveis
                            </h6>
                            
                            <div class="mb-4">
                                <label for="nome" class="form-label">
                                    <i class="bi bi-tag me-2"></i>
                                    Nome da Mídia <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="nome" name="nome" 
                                       value="<?= sanitizeData($midia['nome'] ?? '') ?>" required>
                                <div class="form-text">Nome que será exibido na lista de mídias</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="url_externa" class="form-label">
                                    <i class="bi bi-link-45deg me-2"></i>
                                    URL Externa
                                </label>
                                <input type="url" class="form-control" id="url_externa" name="url_externa" 
                                       value="<?= sanitizeData($midia['url_externa'] ?? '') ?>" 
                                       placeholder="https://exemplo.com/video.mp4">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Para vídeos do YouTube, links externos de imagens ou outros recursos online
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="tempo_exibicao" class="form-label">
                                    <i class="bi bi-clock me-2"></i>
                                    Tempo de Exibição
                                </label>
                                
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tempo_exibicao" name="tempo_exibicao" 
                                           value="<?= (int)($midia['tempo_exibicao'] ?? 0) ?>" min="0" max="3600">
                                    <span class="input-group-text">segundos</span>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Tempo em segundos que a mídia será exibida (0 = tempo padrão do sistema)
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <h6 class="text-muted mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                Informações do Sistema
                            </h6>
                            
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Tipo:</strong></label>
                                        <div>
                                            <?php 
                                            $tipo = $midia['tipo'] ?? 'desconhecido';
                                            $iconeTipo = 'file-earmark';
                                            switch($tipo) {
                                                case 'video':
                                                    $iconeTipo = 'play-circle';
                                                    break;
                                                case 'imagem':
                                                    $iconeTipo = 'image';
                                                    break;
                                                case 'youtube':
                                                    $iconeTipo = 'youtube';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-<?= $iconeTipo ?> me-1"></i>
                                                <?= ucfirst($tipo) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Duração original se existir -->
                                    <?php if (!empty($midia['duracao']) && is_numeric($midia['duracao'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Duração Original:</strong></label>
                                        <p class="form-control-plaintext mb-0">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= formatDurationSafe($midia['duracao']) ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Tamanho do arquivo se existir -->
                                    <?php if (!empty($midia['tamanho_arquivo']) && is_numeric($midia['tamanho_arquivo'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Tamanho do Arquivo:</strong></label>
                                        <p class="form-control-plaintext mb-0">
                                            <i class="bi bi-hdd me-1"></i>
                                            <?= formatFileSizeSafe($midia['tamanho_arquivo']) ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Localização do arquivo se existir -->
                                    <?php if (!empty($midia['caminho_arquivo'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Localização:</strong></label>
                                        <p class="form-control-plaintext mb-0">
                                            <i class="bi bi-folder me-1"></i>
                                            <small class="text-success">Arquivo armazenado localmente</small>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Data de criação -->
                                    <div class="mb-0">
                                        <label class="form-label"><strong>Criada em:</strong></label>
                                        <p class="form-control-plaintext mb-0">
                                            <i class="bi bi-calendar-plus me-1"></i>
                                            <?= formatDateSafe($midia['data_criacao'] ?? null) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões de ação - ambos no lado direito -->
                    <div class="botoes-acao">
                        <a href="midias.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>
                            Voltar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-check-circle me-2"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditarMidia');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        // Adicionar indicador de carregamento
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Salvando...';
        submitBtn.disabled = true;
        
        // Se houver erro, restaurar o botão após timeout
        setTimeout(function() {
            if (submitBtn.disabled) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }, 10000); // 10 segundos timeout
    });
    
    // Validação em tempo real do nome
    const nomeInput = document.getElementById('nome');
    if (nomeInput) {
        nomeInput.addEventListener('input', function() {
            if (this.value.trim().length < 3) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    // Validação da URL
    const urlInput = document.getElementById('url_externa');
    if (urlInput) {
        urlInput.addEventListener('input', function() {
            const url = this.value.trim();
            if (url) {
                if (url.includes('youtube.com') || url.includes('youtu.be')) {
                    const videoId = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/);
                    if (videoId) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                } else {
                    // Para outras URLs, verificar se é uma URL válida
                    try {
                        new URL(url);
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } catch {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });
    }
    
    // Validação do tempo de exibição
    const tempoInput = document.getElementById('tempo_exibicao');
    if (tempoInput) {
        tempoInput.addEventListener('input', function() {
            const tempo = parseInt(this.value);
            if (tempo >= 0 && tempo <= 3600) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/template.php';
?>
