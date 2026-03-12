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
    <title><?= htmlspecialchars($tela['nome'] ?? 'Player') ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        :root {
            --sidebar-width: clamp(280px, 25vw, 400px);
            --ticker-height: clamp(120px, 18vh, 180px);
            --royal-blue: #4169e1;
            --dark-blue-1: #1e3a8a;
            --dark-blue-2: #1e40af;
            --news-yellow: #ffc107;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        html, body {
            width: 100%; height: 100%; background: #000; overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .player-container {
            width: 100vw; height: 100vh; position: relative; display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            grid-template-rows: 1fr var(--ticker-height);
            grid-template-areas: 
                "sidebar content"
                "sidebar ticker";
        }
        
        .sidebar {
            grid-area: sidebar;
            background: linear-gradient(180deg, var(--royal-blue) 0%, var(--dark-blue-1) 30%, var(--dark-blue-2) 70%, var(--dark-blue-1) 100%);
            position: relative; overflow-y: auto; border-right: 3px solid rgba(65, 105, 225, 0.3);
            display: flex; flex-direction: column;
        }
        
        .sidebar-content {
            position: relative; z-index: 2; flex-grow: 1; display: flex; flex-direction: column;
            padding: clamp(15px, 2.5vh, 30px) clamp(10px, 1.5vw, 20px); 
            color: white; justify-content: space-between;
        }
        
        .company-logo {
            width: 100%; margin-top: 1.5vh; max-height: clamp(50px, 10vh, 70px); object-fit: contain; margin-bottom: 2vh;
            filter: brightness(1.2) drop-shadow(0 0 20px rgba(65, 105, 225, 0.6));
            transition: all 0.3s ease; animation: logoPulse 3s ease-in-out infinite;
        }
        
        @keyframes logoPulse { 50% { transform: scale(1.05); } }
        
        .divider {
            width: 100%; height: 2px; background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.8) 20%, rgba(255, 255, 255, 1) 50%, rgba(255, 255, 255, 0.8) 80%, transparent 100%);
            margin: 1.5vh 0; position: relative; border-radius: 1px; box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .top-section { flex: 0 0 auto; }
        .datetime-section { margin: 2vh 0; text-align: center; }
        .date { font-size: clamp(0.7rem, 1.2vw, 0.9rem); font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 1vh; text-transform: uppercase; letter-spacing: 0.8px; }
        .time { font-size: clamp(2.2rem, 6vw, 3.5rem); font-weight: 900; color: #ffffff; line-height: 1; }
        
        .middle-section { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .weather-section { margin: 2vh 0; }
        .weather-location { font-size: clamp(0.75rem, 1.3vw, 1rem); font-weight: 1100; color: rgba(255, 255, 255, 0.9); margin-bottom: 1vh; text-transform: uppercase; }
        .weather-main { display: flex; align-items: center; justify-content: center; margin-bottom: 1.5vh; }
        .weather-icon { font-size: clamp(2.2rem, 5vw, 3.5rem); margin-right: 1.5vw; }
        .weather-temp { font-size: clamp(2.5rem, 6vw, 3.8rem); font-weight: 800; color: #ffffff; }
        .weather-desc { font-size: clamp(0.65rem, 1.1vw, 0.8rem); color: rgba(255, 255, 255, 0.8); margin-bottom: 2vh; }
        .weekly-forecast { margin-bottom: 2vh; }
        .forecast-title { font-size: clamp(0.6rem, 1vw, 0.7rem); font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 1vh; text-transform: uppercase; }
        .forecast-days { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5vw; }
        .forecast-day { background: rgba(255,255,255,0.05); border-radius: 8px; padding: 1vh 0.5vw; text-align: center; border: 1px solid rgba(255, 255, 255, 0.1); }
        .forecast-day-name { font-size: clamp(0.6rem, 1vw, 0.75rem); font-weight: 600; }
        .forecast-day-icon { font-size: clamp(0.8rem, 1.2vw, 1rem); margin: 0.5vh 0; }
        .forecast-day-temp { font-size: clamp(0.6rem, 1vw, 0.7rem); font-weight: 700; }
        .bottom-section { flex: 0 0 auto; }
        .info-section { display: flex; flex-direction: column; font-size: clamp(0.6rem, 1vw, 0.7rem); }
        .info-item { display: flex; justify-content: space-between; align-items: center; padding: 0.8vh 0; border-bottom: 1px solid rgba(65, 105, 225, 0.2); }
        
        .media-content { grid-area: content; position: relative; background: #000; overflow: hidden; }
        .media-content video, .media-content img, .media-content iframe { width: 100%; height: 100%; object-fit: cover; border: none; }
        .media-content iframe.site-frame { object-fit: fill; }
        
        .news-ticker {
            grid-area: ticker; background: linear-gradient(135deg, var(--dark-blue-1) 0%, var(--dark-blue-2) 50%, #1d4ed8 100%);
            position: relative; overflow: hidden; height: 100%; display: flex; align-items: center; padding: 1vh 2vw;
        }
        .news-display { width: 100%; height: 100%; display: flex; flex-direction: column; overflow: hidden; position: relative; }
        .news-display .news-item { position: absolute; width: 100%; height: 100%; display: flex; flex-direction: column; opacity: 0; transition: opacity 0.7s ease-in-out; }
        .news-display .news-item.active { opacity: 1; }
        
        .news-title { 
            font-size: clamp(0.8rem, 1.6vw, 1.1rem); font-weight: 500; color: #ffffff;
            padding-bottom: 0.8vh; margin-bottom: 0.8vh; border-bottom: 1px solid rgba(255, 193, 7, 0.4); 
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
        }
        .news-title strong { font-weight: 800; color: var(--news-yellow); }
        .news-body { 
            flex-grow: 1; background: rgba(0, 0, 0, 0.2); color: #ffffff;
            padding: 1vh 1vw; border-radius: 8px; font-size: clamp(0.6rem, 1.6vw, 1.4rem);
            line-height: 1.45; overflow-y: auto; font-weight: normal;
        }

        .no-content {
            width: 100%; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 30%, #4169e1 60%, #6495ed 100%);
            background-size: 400% 400%; animation: smoothGradient 12s ease-in-out infinite;
            color: #fff; text-align: center; position: relative; overflow: hidden;
        }
        .no-content::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 70%);
            animation: rotate 30s linear infinite; pointer-events: none;
        }
        @keyframes smoothGradient { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes rotate { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .no-content-screen-icon {
            width: 120px; height: 80px; border: 4px solid #ffffff; border-radius: 8px;
            position: relative; margin-bottom: 50px;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            backdrop-filter: blur(10px); animation: screenGlow 3s ease-in-out infinite; z-index: 2;
        }
        .no-content-screen-icon::after {
            content: ''; position: absolute; bottom: -20px; left: 50%;
            transform: translateX(-50%); width: 40px; height: 15px;
            background: #ffffff; border-radius: 0 0 8px 8px;
        }
        .no-content-screen-icon::before {
            content: ''; position: absolute; bottom: 10px; right: 10px;
            width: 8px; height: 8px; background: #00ff88;
            border-radius: 50%; animation: powerBlink 2s ease-in-out infinite;
        }
        @keyframes screenGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(255,255,255,0.3); transform: scale(1); }
            50% { box-shadow: 0 0 40px rgba(255,255,255,0.5); transform: scale(1.05); }
        }
        @keyframes powerBlink { 0%, 50% { opacity: 1; } 51%, 100% { opacity: 0.3; } }
        .no-content-logo {
            font-size: 64px; font-weight: 600; color: #ffffff; margin-bottom: 25px;
            letter-spacing: 6px; position: relative; z-index: 2;
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            text-transform: uppercase; background: linear-gradient(135deg, #ffffff 0%, #e8f4fd 50%, #ffffff 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            animation: textShine 4s ease-in-out infinite; filter: drop-shadow(0 2px 10px rgba(0,0,0,0.2));
        }
        @keyframes textShine {
            0%, 100% { background-position: 0% 50%; transform: translateY(0px); }
            50% { background-position: 100% 50%; transform: translateY(-3px); }
        }
        .no-content-message {
            font-size: 18px; color: rgba(255,255,255,0.8); position: relative; z-index: 2;
            font-weight: 400; letter-spacing: 3px;
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            text-transform: uppercase; animation: fadeInOut 3s ease-in-out infinite;
        }
        @keyframes fadeInOut { 0%, 100% { opacity: 0.8; } 50% { opacity: 1; } }
        
        .fullscreen-btn { position: fixed; top: 20px; right: 20px; width: clamp(40px, 5vw, 60px); height: clamp(40px, 5vw, 60px); background: rgba(65, 105, 225, 0.2); backdrop-filter: blur(20px); border: 2px solid rgba(65, 105, 225, 0.3); border-radius: 15px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: clamp(20px, 3vw, 28px); z-index: 9999; }
        .fullscreen-btn.hidden { opacity: 0; pointer-events: none; }
        .site-loading { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0, 0, 0, 0.9); color: white; padding: 25px 35px; border-radius: 15px; font-size: 18px; z-index: 10; }
        .site-loading .spinner { display: inline-block; width: 24px; height: 24px; border: 3px solid rgba(255, 255, 255, 0.3); border-radius: 50%; border-top-color: #4169e1; animation: spin 1s ease-in-out infinite; margin-right: 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .no-cursor { cursor: none; }
        .sound-indicator {
            position: fixed; bottom: 30px; left: clamp(290px, 26vw, 410px); background: rgba(0, 0, 0, 0.8);
            color: white; padding: 12px 18px; border-radius: 10px; font-size: 16px; font-weight: 500;
            z-index: 9999; opacity: 0; transition: opacity 0.3s ease, background 0.3s ease;
            backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.3); pointer-events: none;
        }
        
        .sound-indicator.show { opacity: 1; }
        .sound-indicator.muted { background: rgba(255, 69, 0, 0.8); border-color: rgba(255, 69, 0, 0.6); }
        .sound-indicator.unmuted { background: rgba(34, 197, 94, 0.8); border-color: rgba(34, 197, 94, 0.6); }
        
    </style>
</head>
<body>
    <div class="player-container">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="top-section">
                    <img src="https://www.ctdi.com/wp-content/uploads/2020/12/ctdi-flat-logo-white-1024x223.png" alt="CTDI Logo" class="company-logo" id="companyLogo">
                    <div class="divider"></div>
                    <div class="datetime-section">
                        <div class="date" id="currentDate">Carregando...</div>
                        <div class="time" id="currentTime">--:--:--</div>
                    </div>
                    <div class="divider"></div>
                </div>
                <div class="middle-section">
                    <div class="weather-section">
                        <div class="weather-location" id="weatherLocation">Campinas, SP</div>
                        <div class="weather-main">
                            <div class="weather-icon" id="weatherIcon">☁️</div>
                            <div class="weather-temp" id="weatherTemp">--°</div>
                        </div>
                        <div class="weather-desc" id="weatherDesc">Carregando...</div>
                        <div class="weekly-forecast">
                            <div class="forecast-title">Próximos Dias</div>
                            <div class="forecast-days" id="weeklyForecast"></div>
                        </div>
                    </div>
                    <div class="divider"></div>
                </div>
                <div class="bottom-section">
                    <div class="info-section">
                        <div class="info-item"> <span class="info-label">Umidade</span> <span class="info-value" id="humidity">--%</span> </div>
                        <div class="info-item"> <span class="info-label">Vento</span> <span class="info-value" id="windSpeed">-- km/h</span> </div>
                        <div class="info-item"> <span class="info-label">Sensação</span> <span class="info-value" id="feelsLike">--°C</span> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="media-content" id="mediaContainer">
            <?php if (empty($midias)): ?>
                <div class="no-content">
                    <div class="no-content-screen-icon"></div>
                    <div class="no-content-logo">Comunica Play</div>
                    <div class="no-content-message">Aguardando conteúdo...</div>
                </div>
            <?php endif; ?>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
        </div>
        <div class="news-ticker">
            <div class="news-display" id="newsDisplay">
                <div class="news-item active">
                    <div class="news-title"><strong>Últimas Notícias</strong></div>
                    <div class="news-body">Por favor, aguarde...</div>
                </div>
            </div>
        </div>
        <button class="fullscreen-btn" id="fullscreenBtn" title="Tela cheia + Som">⛶</button>
        <div class="sound-indicator" id="soundIndicator">🔇 Clique no botão de tela cheia para ativar som</div>
    </div>

    <script>
        const midias = <?= json_encode($midias) ?>;
        let currentIndex = 0;
        let currentTimer = null;
        let currentVideoElement = null;
        let soundEnabled = false;
        let cursorTimer = null;
        let lastForcedCheckTimestamp = 0;
        
        const mediaContainer = document.getElementById('mediaContainer');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const soundIndicator = document.getElementById('soundIndicator');
        const progressBar = document.getElementById('progressBar'); // <-- ADICIONADO
        
        const WEATHER_API_KEY = '1fa2a0de047abd58cbed9975bbf0e963';
        const WEATHER_CITY = 'Piracicaba';
        
        // <-- ADICIONADO: Função para controlar a barra de progresso -->
        function startProgressBar(duration) {
            if (!progressBar) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.transition = `width ${duration / 1000}s linear`;
                progressBar.style.width = '100%';
            }, 100);
        }

        function isWebsiteUrl(url) {
            if (!url) return false;
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.svg', '.ico'];
            if (imageExtensions.some(ext => url.toLowerCase().includes(ext))) {
                return false;
            }
            const websiteDomains = [
                'google.com', 'youtube.com', 'facebook.com', 'instagram.com',
                'twitter.com', 'linkedin.com', 'github.com', 'stackoverflow.com',
                'wikipedia.org', 'reddit.com', 'amazon.com', 'microsoft.com',
                'apple.com', 'netflix.com'
            ];
            if (websiteDomains.some(domain => url.toLowerCase().includes(domain))) {
                return true;
            }
            try {
                const parsedUrl = new URL(url);
                const path = parsedUrl.pathname.toLowerCase();
                if (imageExtensions.some(ext => path.endsWith(ext))) { return false; }
                if (path === '/' || path === '' || path.endsWith('.html') || path.endsWith('.php')) { return true; }
            } catch (e) {
                return false;
            }
            return true;
        }

        function showMedia(index) {
            if (midias.length === 0) return;
            if (index >= midias.length) index = 0;
            if (index < 0) index = midias.length - 1;
            
            currentIndex = index;
            const midia = midias[index];
            
            console.log(`▶️ Reproduzindo mídia ${index + 1}/${midias.length}:`, midia.nome, 'Tipo:', midia.tipo);
            
            mediaContainer.innerHTML = '';
            clearTimeout(currentTimer);
            currentVideoElement = null;

            // <-- ADICIONADO: Reseta a barra de progresso no início da exibição
            if (progressBar) {
                progressBar.style.transition = 'none';
                progressBar.style.width = '0%';
            }
            
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
                    startProgressBar(duration); // <-- ADICIONADO
                } else {
                    setTimeout(nextMedia, 2000);
                }
            } else if (midia.tipo === 'imagem' || midia.tipo === 'link_imagem' || midia.tipo === 'site') {
                const mediaUrl = midia.url_externa || midia.caminho;
                if ((midia.tipo === 'site') || isWebsiteUrl(mediaUrl)) {
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'site-loading';
                    loadingDiv.innerHTML = '<div class="spinner"></div>Carregando site...';
                    mediaContainer.appendChild(loadingDiv);
                    mediaElement = document.createElement('iframe');
                    mediaElement.className = 'site-frame';
                    mediaElement.src = mediaUrl;
                    mediaElement.allow = 'autoplay; encrypted-media; fullscreen';
                    mediaElement.frameBorder = '0';
                    mediaElement.title = midia.nome;
                    mediaElement.addEventListener('load', () => {
                        const loading = mediaContainer.querySelector('.site-loading');
                        if (loading) loading.remove();
                    });
                    mediaElement.addEventListener('error', () => {
                        const loading = mediaContainer.querySelector('.site-loading');
                        if (loading) loading.remove();
                        setTimeout(nextMedia, 2000);
                    });
                    currentTimer = setTimeout(nextMedia, duration);
                    startProgressBar(duration); // <-- ADICIONADO
                } else {
                    mediaElement = document.createElement('img');
                    mediaElement.src = mediaUrl;
                    mediaElement.addEventListener('error', () => setTimeout(nextMedia, 2000));
                    mediaElement.addEventListener('load', () => console.log('✅ Imagem carregada:', midia.nome));
                    currentTimer = setTimeout(nextMedia, duration);
                    startProgressBar(duration); // <-- ADICIONADO
                }
            } else {
                console.warn('⚠️ Tipo de mídia não suportado:', midia.tipo);
                setTimeout(nextMedia, 2000);
            }
            
            if (mediaElement) {
                mediaContainer.appendChild(mediaElement);
            }
        }
        
        function nextMedia() { showMedia(currentIndex + 1); }
        function prevMedia() { showMedia(currentIndex - 1); }
        
        function extractYouTubeId(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }
        
        function handleKeyboard(e) {
            switch(e.key) {
                case 'ArrowRight': case ' ': e.preventDefault(); nextMedia(); break;
                case 'ArrowLeft': e.preventDefault(); prevMedia(); break;
                case 'f': case 'F': e.preventDefault(); activateFullscreenAndSound(); break;
            }
        }
        
        function init() {
            console.log('Player inicializado com', midias.length, 'mídias.');
            setupEventListeners();
            initDateTime();
            initWeather();
            initNews();

            setTimeout(() => {
                soundIndicator.classList.add('show', 'muted');
                setTimeout(() => soundIndicator.classList.remove('show'), 6000);
            }, 2000);

            showCursor();

            if (midias.length > 0) { 
                showMedia(0); 
            }

            sendHeartbeat();
            setInterval(sendHeartbeat, 30000);
            setInterval(checkForcedHeartbeat, 5000);
        }

        function setupEventListeners() {
            fullscreenBtn.addEventListener('click', activateFullscreenAndSound);
            function handleFullscreenChange() {
                if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                    fullscreenBtn.classList.add('hidden');
                } else {
                    fullscreenBtn.classList.remove('hidden');
                    soundEnabled = false;
                    if (currentVideoElement) currentVideoElement.muted = true;
                    showSoundIndicator(true);
                }
            }
            document.addEventListener('fullscreenchange', handleFullscreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.addEventListener('mozfullscreenchange', handleFullscreenChange);
            document.addEventListener('msfullscreenchange', handleFullscreenChange);
            document.addEventListener('keydown', handleKeyboard);
            document.addEventListener('mousemove', showCursor);
            document.addEventListener('mousedown', showCursor);
        }

        function initDateTime() {
            const dateEl = document.getElementById('currentDate');
            const timeEl = document.getElementById('currentTime');
            function updateDateTime() {
                const now = new Date();
                dateEl.textContent = now.toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                timeEl.textContent = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            }
            updateDateTime();
            setInterval(updateDateTime, 1000);
        }

        function initWeather() {
            const weatherIcons = {'01d':'☀️','01n':'🌙','02d':'⛅','02n':'☁️','03d':'☁️','03n':'☁️','04d':'🌧️','04n':'🌧️','09d':'🌧️','09n':'🌧️','10d':'🌦️','10n':'🌧️','11d':'⛈️','11n':'⛈️','13d':'❄️','13n':'❄️','50d':'🌫️','50n':'🌫️'};
            async function fetchCurrentWeather() {
                try {
                    const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${WEATHER_CITY}&appid=${WEATHER_API_KEY}&units=metric&lang=pt_br`);
                    if (!response.ok) throw new Error('Erro na API de clima atual');
                    const data = await response.json();
                    document.getElementById('weatherIcon').textContent = weatherIcons[data.weather[0].icon] || '☁️';
                    document.getElementById('weatherTemp').textContent = `${Math.round(data.main.temp)}°`;
                    document.getElementById('weatherDesc').textContent = data.weather[0].description.charAt(0).toUpperCase() + data.weather[0].description.slice(1);
                    document.getElementById('humidity').textContent = `${data.main.humidity}%`;
                    document.getElementById('windSpeed').textContent = `${Math.round(data.wind.speed * 3.6)} km/h`;
                    document.getElementById('feelsLike').textContent = `${Math.round(data.main.feels_like)}°C`;
                } catch (error) { console.error('Erro ao buscar clima atual:', error); }
            }
            async function fetchForecast() {
                try {
                    const response = await fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${WEATHER_CITY}&appid=${WEATHER_API_KEY}&units=metric&lang=pt_br`);
                    if (!response.ok) throw new Error('Erro na API de previsão semanal');
                    const data = await response.json();
                    const forecastDays = {};
                    data.list.forEach(item => {
                        const date = new Date(item.dt * 1000);
                        const day = date.toISOString().split('T')[0];
                        if (!forecastDays[day] && date.getHours() >= 12) { forecastDays[day] = item; }
                    });
                    const weeklyForecastDiv = document.getElementById('weeklyForecast');
                    weeklyForecastDiv.innerHTML = '';
                    const todayStr = new Date().toISOString().split('T')[0];
                    Object.values(forecastDays).filter(day => day.dt_txt.split(' ')[0] !== todayStr).slice(0, 5)
                        .forEach(day => {
                            const date = new Date(day.dt * 1000);
                            const dayName = date.toLocaleDateString('pt-BR', { weekday: 'short' }).toUpperCase().substring(0, 3);
                            const icon = weatherIcons[day.weather[0].icon] || '☁️';
                            const temp = `${Math.round(day.main.temp)}°`;
                            const dayElement = document.createElement('div');
                            dayElement.className = 'forecast-day';
                            dayElement.innerHTML = `<div class="forecast-day-name">${dayName}</div><div class="forecast-day-icon">${icon}</div><div class="forecast-day-temp">${temp}</div>`;
                            weeklyForecastDiv.appendChild(dayElement);
                        });
                } catch (error) { console.error('Erro ao buscar previsão semanal:', error); }
            }
            fetchCurrentWeather();
            fetchForecast();
            setInterval(fetchCurrentWeather, 10 * 60 * 1000);
            setInterval(fetchForecast, 6 * 60 * 60 * 1000);
        }

        function initNews() {
            let newsData = [];
            let newsIndex = 0;
            const newsDisplay = document.getElementById('newsDisplay');
            async function fetchNews() {
                try {
                    const response = await fetch('/public/fetch-rss.php'); 
                    if (!response.ok) throw new Error(`Erro na rede ou no proxy: ${response.statusText}`);
                    const xmlText = await response.text();
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(xmlText, "application/xml");
                    const items = xmlDoc.querySelectorAll("item");
                    if (items.length === 0) throw new Error("Feed RSS não contém itens.");
                    const fetchedArticles = [];
                    items.forEach(item => {
                        const title = item.querySelector("title")?.textContent || "";
                        const source = item.querySelector("source")?.textContent || "";
                        const descriptionHTML = item.querySelector("description")?.textContent || "";
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = descriptionHTML;
                        const body = tempDiv.querySelector('a')?.textContent || title;
                        if (title) { fetchedArticles.push({ title, body, source }); }
                    });
                    if (fetchedArticles.length > 0) { newsData = fetchedArticles; } 
                    else { throw new Error("Não foi possível extrair notícias."); }
                } catch (error) {
                    console.error('❌ Falha ao buscar notícias RSS:', error);
                    newsData = [{ body: "Não foi possível carregar as notícias.", source: "Sistema" }];
                }
            }
            
            function updateNewsView() {
                if (newsData.length === 0) return;
                const oldNews = newsDisplay.querySelector('.news-item.active');
                if (oldNews) oldNews.classList.remove('active');
                newsIndex = (newsIndex + 1) % newsData.length;
                const nextNews = newsData[newsIndex];
                const finalBody = `${nextNews.body} (${nextNews.source})`;
                const newItem = document.createElement('div');
                newItem.className = 'news-item';
                newItem.innerHTML = `<div class="news-title"><strong>Últimas Notícias</strong></div><div class="news-body">${finalBody}</div>`;
                newsDisplay.appendChild(newItem);
                setTimeout(() => newItem.classList.add('active'), 50);
                if (oldNews) setTimeout(() => oldNews.remove(), 750);
            }
            
            fetchNews().then(() => {
                newsDisplay.innerHTML = '';
                updateNewsView(); 
                setInterval(updateNewsView, 15000);
                setInterval(fetchNews, 15 * 60 * 1000);
            });
        }

        function showSoundIndicator(muted) {
            soundIndicator.classList.remove('muted', 'unmuted');
            soundIndicator.innerHTML = muted ? '🔇 Som desativado' : '🔊 Som ativado';
            soundIndicator.classList.add(muted ? 'muted' : 'unmuted', 'show');
            setTimeout(() => soundIndicator.classList.remove('show'), 3000);
        }
        function showCursor() {
            document.body.classList.remove('no-cursor');
            clearTimeout(cursorTimer);
            cursorTimer = setTimeout(() => document.body.classList.add('no-cursor'), 3000);
        }

        function activateFullscreenAndSound() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }
            soundEnabled = true;
            showSoundIndicator(false);
            document.documentElement.requestFullscreen().catch(e => {
                console.warn('⚠️ Tela cheia bloqueada', e);
            }).finally(() => {
                if (currentVideoElement) currentVideoElement.muted = false;
            });
        }

        function sendHeartbeat() {
            const url = `heartbeat.php?tela=<?= $tela['hash_unico'] ?? $hash ?>&t=${new Date().getTime()}`;
            fetch(url)
                .then(response => { if (!response.ok) throw new Error('Falha no heartbeat'); return response.json(); })
                .then(data => { if (data.success) console.log('📡 Heartbeat OK:', data.timestamp); })
                .catch(error => console.error('❌ Erro no heartbeat:', error));
        }

        async function checkForcedHeartbeat() {
            try {
                const url = `api/check_status.php?t=${new Date().getTime()}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Falha ao verificar status');
                const data = await response.json();
                if (data.request_time && data.request_time > lastForcedCheckTimestamp) {
                    console.log('✅ Requisição de heartbeat forçado recebida!');
                    lastForcedCheckTimestamp = data.request_time;
                    sendHeartbeat();
                }
            } catch (error) {
                console.warn('⚠️ Erro ao verificar atualização forçada:', error.message);
            }
        }
        
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>