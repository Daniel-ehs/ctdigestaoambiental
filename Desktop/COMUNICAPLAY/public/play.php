<?php
require_once __DIR__ . '/../models/Playlist.php';
require_once __DIR__ . '/../models/Tela.php';

// Recebe o hash da tela
$hash = $_GET['hash'] ?? $_GET['tela'] ?? '';

if (empty($hash)) {
    die('Hash da tela não fornecido');
}

try {
    // Busca a tela pelo hash
    $telaModel = new Tela();
    $tela = $telaModel->getByHash($hash);
    
    if (!$tela) {
        die('Tela não encontrada');
    }
    
    // Busca playlist ativa para esta tela
    $playlistModel = new Playlist();
    $playlist = $playlistModel->getPlaylistAtiva($tela['id']);
    
    $midias = [];
    if ($playlist && $playlist['total_midias'] > 0) {
        $midias = $playlistModel->getMidias($playlist['id']);
    }
    
} catch (Exception $e) {
    error_log("Erro no player: " . $e->getMessage());
    die('Erro interno do servidor');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tela['nome']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            background: #000;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        
        .player-container {
            width: 100vw;
            height: 100vh;
            position: relative;
        }
        
        .media-content {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .media-content video,
        .media-content img,
        .media-content iframe {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: none;
            display: block;
        }
        
        /* Estilo específico para sites - iframe responsivo */
        .media-content iframe.site-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background: #fff;
            object-fit: fill; /* Para sites, usar fill ao invés de cover */
        }
        
        /* Indicador de carregamento para sites */
        .site-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 25px 35px;
            border-radius: 15px;
            font-size: 18px;
            z-index: 10;
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        
        .site-loading .spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #4169e1;
            animation: spin 1s ease-in-out infinite;
            margin-right: 12px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Tela de sem conteúdo - Design completamente novo */
        .no-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 30%, #4169e1 60%, #6495ed 100%);
            background-size: 400% 400%;
            animation: smoothGradient 12s ease-in-out infinite;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Efeito de luz ambiente */
        .no-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            pointer-events: none;
        }
        
        @keyframes smoothGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Ícone de tela moderna - usando CSS puro */
        .screen-icon {
            width: 120px;
            height: 80px;
            border: 4px solid #ffffff;
            border-radius: 8px;
            position: relative;
            margin-bottom: 50px;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            backdrop-filter: blur(10px);
            animation: screenGlow 3s ease-in-out infinite;
            z-index: 2;
        }
        
        /* Base do monitor */
        .screen-icon::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 15px;
            background: #ffffff;
            border-radius: 0 0 8px 8px;
        }
        
        /* Ponto de energia */
        .screen-icon::before {
            content: '';
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background: #00ff88;
            border-radius: 50%;
            animation: powerBlink 2s ease-in-out infinite;
        }
        
        @keyframes screenGlow {
            0%, 100% { 
                box-shadow: 0 0 20px rgba(255,255,255,0.3);
                transform: scale(1);
            }
            50% { 
                box-shadow: 0 0 40px rgba(255,255,255,0.5);
                transform: scale(1.05);
            }
        }
        
        @keyframes powerBlink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        
        /* Texto Comunica Play - Tipografia moderna */
        .logo {
            font-size: 64px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 25px;
            letter-spacing: 6px;
            position: relative;
            z-index: 2;
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            text-transform: uppercase;
            background: linear-gradient(135deg, #ffffff 0%, #e8f4fd 50%, #ffffff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textShine 4s ease-in-out infinite;
            filter: drop-shadow(0 2px 10px rgba(0,0,0,0.2));
        }
        
        @keyframes textShine {
            0%, 100% { 
                background-position: 0% 50%;
                transform: translateY(0px);
            }
            50% { 
                background-position: 100% 50%;
                transform: translateY(-3px);
            }
        }
        
        /* Texto aguardando conteúdo */
        .waiting-message {
            font-size: 18px;
            color: rgba(255,255,255,0.8);
            position: relative;
            z-index: 2;
            font-weight: 400;
            letter-spacing: 3px;
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            text-transform: uppercase;
            animation: fadeInOut 3s ease-in-out infinite;
        }
        
        @keyframes fadeInOut {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }
        
        /* Botão de tela cheia - sempre visível quando há mídia */
        .fullscreen-btn {
            position: fixed;
            top: 30px;
            right: 30px;
            width: 70px;
            height: 70px;
            background: rgba(0, 0, 0, 0.8);
            border: 3px solid rgba(255, 255, 255, 0.6);
            border-radius: 15px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            z-index: 9999;
            transition: all 0.3s ease;
            opacity: 0.9;
            backdrop-filter: blur(10px);
        }
        
        .fullscreen-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 1);
            opacity: 1;
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .fullscreen-btn:active {
            transform: scale(1.05);
        }
        
        /* Esconde botão quando em tela cheia */
        .fullscreen .fullscreen-btn {
            display: none;
        }
        
        /* Remove cursor após inatividade */
        .no-cursor {
            cursor: none;
        }
        
        .hidden {
            display: none !important;
        }
        
        /* Indicador de som */
        .sound-indicator {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 18px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .sound-indicator.show {
            opacity: 1;
        }
        
        .sound-indicator.muted {
            background: rgba(255, 69, 0, 0.8);
            border-color: rgba(255, 69, 0, 0.6);
        }
        
        .sound-indicator.unmuted {
            background: rgba(34, 197, 94, 0.8);
            border-color: rgba(34, 197, 94, 0.6);
        }
    </style>
</head>
<body>
    <div class="player-container">
        <?php if (empty($midias)): ?>
            <!-- Tela de "sem conteúdo" -->
            <div class="no-content">
                <div class="screen-icon"></div>
                <div class="logo">Comunica Play</div>
                <div class="waiting-message">Aguardando conteúdo...</div>
            </div>
        <?php else: ?>
            <!-- Player de mídia -->
            <div class="media-content" id="mediaContainer"></div>
            
            <!-- Botão de tela cheia -->
            <button class="fullscreen-btn" id="fullscreenBtn" title="Tela cheia + Som">
                ⛶
            </button>
            
            <!-- Indicador de som -->
            <div class="sound-indicator" id="soundIndicator">
                🔇 Clique no botão de tela cheia para ativar som
            </div>
        <?php endif; ?>
    </div>

    <!-- SCRIPT GERAL (sempre carregado para garantir o heartbeat) -->
    <script>
        // --- LÓGICA DE HEARTBEAT E ATUALIZAÇÃO FORÇADA (SEMPRE ATIVA) ---
        let lastForcedCheckTimestamp = 0;

        // Função que envia o sinal de "online" para o servidor
        function sendHeartbeat() {
            // Adiciona um parâmetro único para evitar cache do navegador
            const url = `heartbeat.php?tela=<?= $tela['hash_unico'] ?>&t=${new Date().getTime()}`;
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Falha na resposta do heartbeat');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('📡 Heartbeat enviado:', data.timestamp);
                    } else {
                         console.error('Falha no heartbeat:', data.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Erro na requisição do heartbeat:', error);
                });
        }

        // Verifica periodicamente se o dashboard solicitou uma atualização
        async function checkForcedHeartbeat() {
            try {
                // Adiciona um parâmetro único para evitar cache do navegador
                const url = `api/check_status.php?t=${new Date().getTime()}`;
                const response = await fetch(url);
                
                if (!response.ok) throw new Error('Falha ao verificar status');

                const data = await response.json();

                // Se houver um novo timestamp de requisição, envia um heartbeat
                if (data.request_time && data.request_time > lastForcedCheckTimestamp) {
                    console.log('✅ Requisição de heartbeat forçado recebida do dashboard!');
                    
                    // Armazena o timestamp da última requisição atendida
                    lastForcedCheckTimestamp = data.request_time;
                    
                    // Envia imediatamente o sinal de "online"
                    sendHeartbeat();
                }
            } catch (error) {
                // Não é um erro crítico, apenas um aviso no console.
                console.warn('⚠️ Não foi possível verificar por atualização forçada:', error.message);
            }
        }
        
        // Inicia os heartbeats
        sendHeartbeat(); // Primeiro heartbeat imediato
        setInterval(sendHeartbeat, 30000); // Heartbeat regular a cada 30 segundos
        setInterval(checkForcedHeartbeat, 5000); // Verifica por sinal de atualização a cada 5 segundos

        // --- LÓGICA DE REPRODUÇÃO DE MÍDIA (SÓ EXECUTA SE HOUVER MÍDIAS) ---
        const midias = <?= json_encode($midias) ?>;
        
        if (midias.length > 0) {
            let currentIndex = 0;
            let currentTimer = null;
            let cursorTimer = null;
            let currentVideoElement = null;
            let soundEnabled = false;
            
            const mediaContainer = document.getElementById('mediaContainer');
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            const soundIndicator = document.getElementById('soundIndicator');
            
            console.log('Player carregado com', midias.length, 'mídias');
            
            setTimeout(() => {
                soundIndicator.classList.add('show', 'muted');
            }, 2000);
            
            setTimeout(() => {
                soundIndicator.classList.remove('show');
            }, 7000);
            
            function showSoundIndicator(muted) {
                soundIndicator.classList.remove('muted', 'unmuted');
                if (muted) {
                    soundIndicator.innerHTML = '🔇 Som desativado';
                    soundIndicator.classList.add('muted');
                } else {
                    soundIndicator.innerHTML = '🔊 Som ativado';
                    soundIndicator.classList.add('unmuted');
                }
                soundIndicator.classList.add('show');
                
                setTimeout(() => {
                    soundIndicator.classList.remove('show');
                }, 3000);
            }
            
            function activateFullscreenAndSound() {
                console.log('🚀 Ativando tela cheia e som...');
                soundEnabled = true;
                
                document.documentElement.requestFullscreen().catch(e => {
                    console.log('⚠️ Não foi possível entrar em tela cheia:', e);
                });

                if (currentVideoElement) {
                    currentVideoElement.muted = false;
                    showSoundIndicator(false);
                }
            }
            
            fullscreenBtn.addEventListener('click', activateFullscreenAndSound);
            
            document.addEventListener('fullscreenchange', () => {
                if (document.fullscreenElement) {
                    document.body.classList.add('fullscreen');
                } else {
                    document.body.classList.remove('fullscreen');
                }
            });
            
            function showCursor() {
                document.body.classList.remove('no-cursor');
                clearTimeout(cursorTimer);
                cursorTimer = setTimeout(() => {
                    document.body.classList.add('no-cursor');
                }, 3000);
            }
            
            document.addEventListener('mousemove', showCursor);
            document.addEventListener('mousedown', showCursor);
            showCursor();
            
            // NOVA FUNÇÃO: Detectar se URL é de site ou imagem
            function isWebsiteUrl(url) {
                if (!url) return false;
                
                // Extensões de imagem comuns
                const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.svg', '.ico'];
                
                // Verifica se a URL termina com extensão de imagem
                const hasImageExtension = imageExtensions.some(ext => 
                    url.toLowerCase().includes(ext)
                );
                
                // Se tem extensão de imagem, é imagem
                if (hasImageExtension) {
                    return false;
                }
                
                // Domínios que são claramente sites (não imagens)
                const websiteDomains = [
                    'google.com', 'youtube.com', 'facebook.com', 'instagram.com',
                    'twitter.com', 'linkedin.com', 'github.com', 'stackoverflow.com',
                    'wikipedia.org', 'reddit.com', 'amazon.com', 'microsoft.com',
                    'apple.com', 'netflix.com', 'spotify.com'
                ];
                
                // Verifica se contém domínio conhecido de site
                const isKnownWebsite = websiteDomains.some(domain => 
                    url.toLowerCase().includes(domain)
                );
                
                if (isKnownWebsite) {
                    return true;
                }
                
                // Se a URL não tem extensão específica e parece ser uma página
                // (contém apenas domínio ou termina com / ou contém parâmetros)
                const urlPattern = /^https?:\/\/[^\/]+\/?(\?.*)?$/;
                if (urlPattern.test(url)) {
                    return true;
                }
                
                // Se contém palavras típicas de sites
                const websiteKeywords = ['/home', '/index', '/page', '/site', '/portal', '/dashboard'];
                const hasWebsiteKeywords = websiteKeywords.some(keyword => 
                    url.toLowerCase().includes(keyword)
                );
                
                return hasWebsiteKeywords;
            }
            
            function showMedia(index) {
                if (index >= midias.length) index = 0;
                if (index < 0) index = midias.length - 1;
                
                currentIndex = index;
                const midia = midias[index];
                
                console.log(`▶️ Reproduzindo mídia ${index + 1}/${midias.length}:`, midia.nome, 'Tipo:', midia.tipo);
                
                mediaContainer.innerHTML = '';
                clearTimeout(currentTimer);
                currentVideoElement = null;
                
                let mediaElement = null;
                let duration = parseInt(midia.tempo_exibicao) * 1000;
                
                if (midia.tipo === 'video') {
                    mediaElement = document.createElement('video');
                    mediaElement.src = midia.caminho;
                    mediaElement.autoplay = true;
                    mediaElement.muted = !soundEnabled;
                    mediaElement.loop = false;
                    mediaElement.controls = false;
                    mediaElement.preload = 'auto';
                    mediaElement.playsInline = true;
                    currentVideoElement = mediaElement;
                    
                    mediaElement.addEventListener('ended', nextMedia);
                    mediaElement.addEventListener('error', () => setTimeout(nextMedia, 2000));
                    
                } else if (midia.tipo === 'youtube') {
                    const videoId = extractYouTubeId(midia.caminho);
                    if (videoId) {
                        mediaElement = document.createElement('iframe');
                        const muteParam = soundEnabled ? '0' : '1';
                        mediaElement.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=${muteParam}&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3&fs=0&disablekb=1&loop=1&playlist=${videoId}`;
                        mediaElement.allow = 'autoplay; encrypted-media';
                        mediaElement.frameBorder = '0';
                        currentTimer = setTimeout(nextMedia, duration);
                    } else {
                        setTimeout(nextMedia, 2000);
                    }
                } else if (midia.tipo === 'imagem' || midia.tipo === 'link_imagem') {
                    // CORREÇÃO PRINCIPAL: Detectar automaticamente se é site ou imagem
                    const mediaUrl = midia.url_externa || midia.caminho;
                    
                    if (isWebsiteUrl(mediaUrl)) {
                        // É um SITE - usar iframe
                        console.log('🌐 URL detectada como SITE:', mediaUrl);
                        
                        // Criar indicador de carregamento
                        const loadingDiv = document.createElement('div');
                        loadingDiv.className = 'site-loading';
                        loadingDiv.innerHTML = '<div class="spinner"></div>Carregando site...';
                        mediaContainer.appendChild(loadingDiv);
                        
                        // Criar iframe para o site
                        mediaElement = document.createElement('iframe');
                        mediaElement.className = 'site-frame';
                        mediaElement.src = mediaUrl;
                        mediaElement.allow = 'autoplay; encrypted-media; fullscreen';
                        mediaElement.frameBorder = '0';
                        mediaElement.title = midia.nome;
                        
                        // Event listeners para o iframe
                        mediaElement.addEventListener('load', () => {
                            console.log('✅ Site carregado com sucesso:', midia.nome);
                            // Remove indicador de carregamento
                            const loading = mediaContainer.querySelector('.site-loading');
                            if (loading) loading.remove();
                        });
                        
                        mediaElement.addEventListener('error', (e) => {
                            console.error('❌ Erro ao carregar site:', midia.nome, e);
                            // Remove indicador de carregamento e mostra próxima mídia
                            const loading = mediaContainer.querySelector('.site-loading');
                            if (loading) loading.remove();
                            setTimeout(nextMedia, 2000);
                        });
                        
                        // Timer para próxima mídia
                        currentTimer = setTimeout(() => {
                            console.log('⏰ Timer do site ativado, próxima mídia');
                            nextMedia();
                        }, duration);
                        
                        // Remove indicador de carregamento após 5 segundos (fallback)
                        setTimeout(() => {
                            const loading = mediaContainer.querySelector('.site-loading');
                            if (loading) loading.remove();
                        }, 5000);
                        
                    } else {
                        // É uma IMAGEM - usar img
                        console.log('🖼️ URL detectada como IMAGEM:', mediaUrl);
                        
                        mediaElement = document.createElement('img');
                        mediaElement.src = mediaUrl;
                        
                        mediaElement.addEventListener('error', () => {
                            console.error('❌ Erro ao carregar imagem:', midia.nome);
                            setTimeout(nextMedia, 2000);
                        });
                        mediaElement.addEventListener('load', () => {
                            console.log('✅ Imagem carregada com sucesso:', midia.nome);
                        });
                        currentTimer = setTimeout(nextMedia, duration);
                    }
                    
                } else if (midia.tipo === 'site') {
                    // FUNCIONALIDADE EXPLÍCITA: Tipo site
                    console.log('🌐 Carregando site (tipo explícito):', midia.nome, 'URL:', midia.url_externa || midia.caminho);
                    
                    // Criar indicador de carregamento
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'site-loading';
                    loadingDiv.innerHTML = '<div class="spinner"></div>Carregando site...';
                    mediaContainer.appendChild(loadingDiv);
                    
                    // Criar iframe para o site
                    mediaElement = document.createElement('iframe');
                    mediaElement.className = 'site-frame';
                    
                    // Usar url_externa se disponível, senão usar caminho
                    const siteUrl = midia.url_externa || midia.caminho;
                    mediaElement.src = siteUrl;
                    mediaElement.allow = 'autoplay; encrypted-media; fullscreen';
                    mediaElement.frameBorder = '0';
                    mediaElement.title = midia.nome;
                    
                    // Event listeners para o iframe
                    mediaElement.addEventListener('load', () => {
                        console.log('✅ Site carregado com sucesso:', midia.nome);
                        // Remove indicador de carregamento
                        const loading = mediaContainer.querySelector('.site-loading');
                        if (loading) loading.remove();
                    });
                    
                    mediaElement.addEventListener('error', (e) => {
                        console.error('❌ Erro ao carregar site:', midia.nome, e);
                        // Remove indicador de carregamento e mostra próxima mídia
                        const loading = mediaContainer.querySelector('.site-loading');
                        if (loading) loading.remove();
                        setTimeout(nextMedia, 2000);
                    });
                    
                    // Timer para próxima mídia
                    currentTimer = setTimeout(() => {
                        console.log('⏰ Timer do site ativado, próxima mídia');
                        nextMedia();
                    }, duration);
                    
                    // Remove indicador de carregamento após 5 segundos (fallback)
                    setTimeout(() => {
                        const loading = mediaContainer.querySelector('.site-loading');
                        if (loading) loading.remove();
                    }, 5000);
                    
                } else {
                    console.warn('⚠️ Tipo de mídia não suportado:', midia.tipo);
                    setTimeout(nextMedia, 2000);
                }
                
                if (mediaElement) {
                    mediaContainer.appendChild(mediaElement);
                }
            }
            
            function nextMedia() {
                showMedia(currentIndex + 1);
            }
            
            function extractYouTubeId(url) {
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                const match = url.match(regExp);
                return (match && match[2].length === 11) ? match[2] : null;
            }
            
            window.addEventListener('load', () => {
                showMedia(0);
            });
            
            // Controles de teclado
            document.addEventListener('keydown', (e) => {
                switch(e.key) {
                    case 'ArrowRight':
                    case ' ':
                        e.preventDefault();
                        nextMedia();
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        showMedia(currentIndex - 1);
                        break;
                    case 'f':
                    case 'F':
                        e.preventDefault();
                        activateFullscreenAndSound();
                        break;
                }
            });
        }
    </script>
</body>
</html>
