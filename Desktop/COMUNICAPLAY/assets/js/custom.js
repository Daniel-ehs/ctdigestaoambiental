// Comunica Play - Scripts Customizados

$(document).ready(function() {
    // Inicialização
    initializeApp();
    
    // Event Listeners
    setupEventListeners();
    
    // Auto-refresh para status das telas
    if (window.location.pathname.includes('dashboard')) {
        setInterval(updateTelaStatus, 30000); // 30 segundos
    }
});

function initializeApp() {
    // Inicializa tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializa popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Marca item ativo no menu
    markActiveMenuItem();
    
    // Inicializa drag and drop se existir
    initializeSortable();
}

function setupEventListeners() {
    // Toggle sidebar em mobile
    $(document).on('click', '.sidebar-toggle', function() {
        $('.sidebar').toggleClass('show');
    });
    
    // Fechar sidebar ao clicar fora em mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar, .sidebar-toggle').length) {
                $('.sidebar').removeClass('show');
            }
        }
    });
    
    // Confirmação de exclusão
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var url = $(this).attr('href') || $(this).data('url');
        var message = $(this).data('message') || 'Tem certeza que deseja excluir este item?';
        
        Swal.fire({
            title: 'Confirmar Exclusão',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if ($(this).is('form')) {
                    $(this).submit();
                } else {
                    window.location.href = url;
                }
            }
        });
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Preview de upload de arquivos
    $(document).on('change', 'input[type="file"]', function() {
        previewFile(this);
        if (this.id === 'videoFile' && this.files && this.files[0]) {
            var video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = function() {
                window.URL.revokeObjectURL(video.src);
                $('#videoDuracao').val(Math.round(video.duration));
            };
            video.src = URL.createObjectURL(this.files[0]);
        }
    });
    
    // Validação de formulários
    $(document).on('submit', 'form', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });
}

function markActiveMenuItem() {
    var currentPath = window.location.pathname;
    $('.nav-link').removeClass('active');
    
    $('.nav-link').each(function() {
        var href = $(this).attr('href');
        if (href && currentPath.includes(href.replace('/', ''))) {
            $(this).addClass('active');
        }
    });
}

function initializeSortable() {
    var sortableElements = document.querySelectorAll('.sortable-list');
    
    sortableElements.forEach(function(element) {
        new Sortable(element, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                updatePlaylistOrder();
            }
        });
    });
}

function updateTelaStatus() {
    $.ajax({
        url: '/api/telas/status',
        method: 'GET',
        success: function(data) {
            if (data.success) {
                updateStatusCards(data.summary);
                updateStatusTable(data.telas);
            }
        },
        error: function() {
            console.log('Erro ao atualizar status das telas');
        }
    });
}

function updateStatusCards(summary) {
    $('#total-telas').text(summary.total || 0);
    $('#telas-online').text(summary.online || 0);
    $('#telas-offline').text(summary.offline || 0);
}

function updateStatusTable(telas) {
    var tbody = $('#telas-status tbody');
    tbody.empty();
    
    telas.forEach(function(tela) {
        var statusClass = tela.status === 'online' ? 'status-online' : 'status-offline';
        var statusText = tela.status === 'online' ? 'Online' : 'Offline';
        var ultimaVerificacao = tela.ultima_verificacao ? 
            formatDateTime(tela.ultima_verificacao) : 'Nunca';
        
        var row = `
            <tr>
                <td>${tela.nome}</td>
                <td>
                    <span class="status-indicator ${statusClass}">
                        <span class="status-dot ${tela.status}"></span>
                        ${statusText}
                    </span>
                </td>
                <td>${ultimaVerificacao}</td>
            </tr>
        `;
        
        tbody.append(row);
    });
}

function previewFile(input) {
    console.log('previewFile called with input:', input);
    console.log('input.files:', input.files);
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var preview = $(input).siblings('.file-preview');
            if (preview.length === 0) {
                preview = $(\'<div class="file-preview mt-2"></div>\');
                $(input).closest(\'div.mb-3\').append(preview);
            }
            
            if (file.type.startsWith('image/')) {
                preview.html(`<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">`);
            } else if (file.type.startsWith('video/')) {
                preview.html(`<video controls style="max-width: 200px;"><source src="${e.target.result}"></video>`);
            } else {
                preview.html(`<p class="text-muted">Arquivo selecionado: ${file.name}</p>`);
            }
        };
        
        reader.readAsDataURL(file);
    }
}

function validateForm(form) {
    var isValid = true;
    var $form = $(form);
    
    // Remove mensagens de erro anteriores
    $form.find('.invalid-feedback').remove();
    $form.find('.is-invalid').removeClass('is-invalid');
    
    // Valida campos obrigatórios
    $form.find('[required]').each(function() {
        var $field = $(this);
        var value = $field.val().trim();
        
        if (!value) {
            showFieldError($field, 'Este campo é obrigatório.');
            isValid = false;
        }
    });
    
    // Valida emails
    $form.find('input[type="email"]').each(function() {
        var $field = $(this);
        var value = $field.val().trim();
        
        if (value && !isValidEmail(value)) {
            showFieldError($field, 'Email inválido.');
            isValid = false;
        }
    });
    
    // Valida URLs
    $form.find('input[type="url"]').each(function() {
        var $field = $(this);
        var value = $field.val().trim();
        
        if (value && !isValidUrl(value)) {
            showFieldError($field, 'URL inválida.');
            isValid = false;
        }
    });
    
    // Valida datas
    $form.find('input[type="datetime-local"]').each(function() {
        var $field = $(this);
        var value = $field.val();
        
        if (value) {
            var date = new Date(value);
            if (isNaN(date.getTime())) {
                showFieldError($field, 'Data inválida.');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function showFieldError($field, message) {
    $field.addClass('is-invalid');
    $field.after(`<div class="invalid-feedback">${message}</div>`);
}

function isValidEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

function formatDateTime(dateString) {
    var date = new Date(dateString);
    return date.toLocaleString('pt-BR');
}

function formatDuration(seconds) {
    if (seconds < 60) {
        return seconds + 's';
    } else if (seconds < 3600) {
        var minutes = Math.floor(seconds / 60);
        var remainingSeconds = seconds % 60;
        return minutes + 'm' + (remainingSeconds > 0 ? ' ' + remainingSeconds + 's' : '');
    } else {
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var remainingSeconds = seconds % 60;
        return hours + 'h' + (minutes > 0 ? ' ' + minutes + 'm' : '') + 
               (remainingSeconds > 0 ? ' ' + remainingSeconds + 's' : '');
    }
}

function showAlert(type, message) {
    var alertClass = 'alert-' + type;
    var iconClass = type === 'success' ? 'bi-check-circle' : 
                   type === 'danger' ? 'bi-exclamation-triangle' : 
                   type === 'warning' ? 'bi-exclamation-triangle' : 'bi-info-circle';
    
    var alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bi ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#alerts-container').prepend(alert);
    
    // Auto-hide após 5 segundos
    setTimeout(function() {
        $('.alert').first().fadeOut();
    }, 5000);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('success', 'Link copiado para a área de transferência!');
    }).catch(function() {
        // Fallback para navegadores mais antigos
        var textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('success', 'Link copiado para a área de transferência!');
    });
}

function updatePlaylistOrder() {
    var playlistId = $('#playlist-id').val();
    if (!playlistId) return;
    
    var midias = [];
    $('.sortable-item').each(function(index) {
        var midiaId = $(this).data('midia-id');
        var tempoExibicao = $(this).find('.tempo-exibicao').val() || 10;
        
        midias.push({
            midia_id: midiaId,
            tempo_exibicao: parseInt(tempoExibicao),
            ordem: index + 1
        });
    });
    
    $.ajax({
        url: '/api/playlists/' + playlistId + '/midias',
        method: 'POST',
        data: {
            midias: JSON.stringify(midias)
        },
        success: function(data) {
            if (data.success) {
                showAlert('success', 'Ordem da playlist atualizada!');
            } else {
                showAlert('danger', data.message || 'Erro ao atualizar playlist.');
            }
        },
        error: function() {
            showAlert('danger', 'Erro ao comunicar com o servidor.');
        }
    });
}

// Funções específicas para diferentes páginas

// Dashboard
function refreshDashboard() {
    location.reload();
}

// Mídias
function selectMidia(element) {
    $(element).toggleClass('selected');
    updateSelectedCount();
}

function updateSelectedCount() {
    var count = $('.media-item.selected').length;
    $('#selected-count').text(count);
    $('#btn-add-selected').prop('disabled', count === 0);
}

function addSelectedMidias() {
    var selectedMidias = [];
    $('.media-item.selected').each(function() {
        selectedMidias.push({
            midia_id: $(this).data('midia-id'),
            tempo_exibicao: 10 // Tempo padrão
        });
    });
    
    if (selectedMidias.length > 0) {
        addMidiasToPlaylist(selectedMidias);
    }
}

function addMidiasToPlaylist(midias) {
    var playlistContainer = $('.sortable-list');
    
    midias.forEach(function(midia) {
        var item = createPlaylistItem(midia);
        playlistContainer.append(item);
    });
    
    // Limpa seleção
    $('.media-item').removeClass('selected');
    updateSelectedCount();
    
    showAlert('success', midias.length + ' mídia(s) adicionada(s) à playlist!');
}

function createPlaylistItem(midia) {
    return `
        <div class="sortable-item" data-midia-id="${midia.midia_id}">
            <div class="d-flex align-items-center">
                <div class="media-info flex-grow-1">
                    <div class="media-title">${midia.nome || 'Mídia'}</div>
                    <div class="media-meta">${midia.tipo || 'Tipo desconhecido'}</div>
                </div>
                <div class="me-3">
                    <label class="form-label">Tempo (s):</label>
                    <input type="number" class="form-control tempo-exibicao" 
                           value="${midia.tempo_exibicao}" min="1" max="7200" style="width: 80px;">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromPlaylist(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
}

function removeFromPlaylist(button) {
    $(button).closest('.sortable-item').remove();
    showAlert('info', 'Mídia removida da playlist.');
}

// Telas
function regenerateTelaHash(telaId) {
    if (!confirm('Tem certeza? O link atual da tela será invalidado.')) {
        return;
    }
    
    $.ajax({
        url: '/api/telas/' + telaId + '/regenerate-hash',
        method: 'POST',
        success: function(data) {
            if (data.success) {
                location.reload();
            } else {
                showAlert('danger', data.message || 'Erro ao regenerar link.');
            }
        },
        error: function() {
            showAlert('danger', 'Erro ao comunicar com o servidor.');
        }
    });
}

// Utilitários globais
window.ComunicaPlay = {
    showAlert: showAlert,
    copyToClipboard: copyToClipboard,
    formatDateTime: formatDateTime,
    formatDuration: formatDuration,
    updatePlaylistOrder: updatePlaylistOrder,
    selectMidia: selectMidia,
    addSelectedMidias: addSelectedMidias,
    removeFromPlaylist: removeFromPlaylist,
    regenerateTelaHash: regenerateTelaHash
};



// Função para extrair o ID do vídeo do YouTube de uma URL
function getYouTubeId(url) {
    var ID = 
'';
    url = url.replace(/(>|<)/gi, 
'').split(/(vi\/|v=|\[/|\/v\/|embed\/|\/|youtu.be\/)/);
    if (url[2] !== undefined) {
        ID = url[2].split(/[^0-9a-z_\-]/i);
        ID = ID[0];
    } else {
        ID = url;
    }
    return ID;
}

// Event listener para o campo de URL do YouTube
$(document).on(
'input', 
'#youtubeUrl', function() {
    var url = $(this).val();
    var videoId = getYouTubeId(url);

    if (videoId) {
        if (player) {
            player.loadVideoById(videoId);
        } else {
            player = new YT.Player(
'player', {
                height: 
'0', // O player não precisa ser visível
                width: 
'0',
                videoId: videoId,
                events: {
                    
'onReady': onPlayerReady,
                    
'onStateChange': onPlayerStateChange
                }
            });
        }
    }
});

// Callback quando o player está pronto
function onPlayerReady(event) {
    // O player está pronto, mas não fazemos nada aqui ainda
}

// Callback quando o estado do player muda
function onPlayerStateChange(event) {
    // Quando o vídeo é carregado (estado 5 - cued), obtemos a duração
    if (event.data == YT.PlayerState.CUED) {
        var duration = player.getDuration();
        $(
'#youtubeDuracao').val(Math.round(duration));
    }
}


