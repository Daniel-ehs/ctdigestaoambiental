<?php

require_once __DIR__ .
    '/../includes/session.php';
require_once __DIR__ .
    '/../includes/functions.php';

// Verifica se está logado
SessionManager::requireLogin();

$user = SessionManager::getUser();
$currentPage = basename($_SERVER["PHP_SELF"], ".php");

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Comunica Play</title>
    
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #fff; /* Garante fundo branco para a página */
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        .main-header .logo {
            animation: pulse 2s infinite;
        }

        .sidebar-nav {
            list-style: none;
            padding-left: 0;
        }

        .sidebar-nav .nav-item {
            list-style: none;
        }

        .main-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px; /* Largura da sidebar */
            padding-top: 10px;
            padding-bottom: 20px; /* Padding normal, sem espaço extra para footer fixo */
        }

        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background-color: #343a40;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1030; /* Z-index alto para o header */
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: 250px;
            height: calc(100vh - 60px);
            background-color: #f8f9fa;
            padding-top: 20px;
            overflow-y: auto;
            z-index: 1020; /* Z-index alto para a sidebar */
            border-right: 1px solid #e9ecef;
        }

        .nav-item.has-submenu > .nav-link {
            position: relative;
        }

        .nav-item.has-submenu > .nav-link::after {
            content: '\F282';
            font-family: "bootstrap-icons";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }

        .nav-item.has-submenu.show > .nav-link::after {
            transform: translateY(-50%) rotate(180deg);
        }

        .submenu {
            list-style: none;
            padding-left: 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .submenu.show {
            max-height: 200px;
        }

        .submenu .nav-link {
            padding-left: 35px;
        }

        /* CORREÇÃO 1: Footer no final da página ocupando largura total e à frente de todos */
        .main-footer {
            background-color: #f8f9fa;
            color: #343a40;
            text-align: center;
            padding: 15px 0;
            font-size: 0.9rem;
            border-top: 1px solid #e9ecef;
            margin-left: 0; /* Remove margem para ocupar largura total */
            width: 100%; /* Largura total da página */
            z-index: 1050; /* Z-index mais alto que header (1030) e sidebar (1020) */
            position: relative;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1); /* Sombra sutil para destacar */
        }

        /* CORREÇÃO 2: Ícone de sair moderno sem borda */
        .btn-logout {
            background: transparent; /* Fundo transparente como o header */
            border: none; /* Remove a borda branca */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: #fff;
            text-decoration: none;
            margin-left: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: visible; /* Permite que o tooltip apareça */
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.1); /* Fundo sutil no hover */
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-logout > .bi {
            font-size: 1.2rem;
            transition: transform 0.2s ease-in-out;
            z-index: 1;
        }

        .btn-logout:hover > .bi {
            transform: scale(1.1) rotate(-10deg);
        }

        /* Tooltip melhorado para o botão de sair */
        .btn-logout::after {
            content: attr(title);
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 1060;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .btn-logout:hover::after {
            opacity: 1;
            visibility: visible;
        }

        .btn-logout::before {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-bottom-color: #333;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 1060;
        }

        .btn-logout:hover::before {
            opacity: 1;
            visibility: visible;
        }

        /* Responsividade para dispositivos móveis */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 80px;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-footer {
                margin-left: 0; /* Mantém largura total em dispositivos móveis */
                width: 100%;
            }
        }

    </style>
</head>
<body>
    <div class="main-wrapper">
        <header class="main-header">
            <div class="logo">
                <i class="bi bi-tv"></i>
                Comunica Play
            </div>
            
            <div class="user-info d-flex align-items-center">
                <span class="user-name"><?= sanitize($user["nome"]) ?></span>
                
                <a href="/public/logout.php" class="btn-logout" title="Sair do sistema">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </header>
        
        <nav class="sidebar">
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="/public/dashboard.php" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/public/midias.php" class="nav-link <?= $currentPage === 'midias' || $currentPage === 'midia_adicionar' ? 'active' : '' ?>">
                        <i class="bi bi-collection-play"></i>
                        Mídias
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/public/playlists.php" class="nav-link <?= $currentPage === 'playlists' ? 'active' : '' ?>">
                        <i class="bi bi-list-ul"></i>
                        Playlists
                    </a>
                </li>
                
                <?php if ($user["tipo"] === 'administrador'): ?>
                    <li class="nav-item has-submenu">
                        <a href="#adminSubmenu" data-bs-toggle="collapse" class="nav-link <?= in_array($currentPage, ['telas', 'usuarios']) ? 'active' : '' ?>">
                            <i class="bi bi-gear"></i>
                            Administração
                        </a>
                        <ul class="submenu collapse <?= in_array($currentPage, ['telas', 'usuarios']) ? 'show' : '' ?>" id="adminSubmenu">
                            <li class="nav-item">
                                <a href="/public/telas.php" class="nav-link <?= $currentPage === 'telas' ? 'active' : '' ?>">
                                    <i class="bi bi-display"></i>
                                    Telas
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="/public/usuarios.php" class="nav-link <?= $currentPage === 'usuarios' ? 'active' : '' ?>">
                                    <i class="bi bi-people"></i>
                                    Usuários
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <main class="main-content">
            <div id="alerts-container">
                <?php
                $flashMessages = SessionManager::getFlashMessages();
                foreach ($flashMessages as $message):
                ?>
                    <div class="alert alert-<?= $message["type"] ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $message["type"] === 'success' ? 'check-circle' : ($message["type"] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                        <?= sanitize($message["message"]) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="/public/dashboard.php">
                                <i class="bi bi-house"></i>
                                Início
                            </a>
                        </li>
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <?php if ($index === count($breadcrumb) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?= sanitize($item['title'] ?? '') ?>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <?php if (isset($item["url"])): ?>
                                        <a href="<?= sanitize($item["url"]) ?>"><?= sanitize($item['title'] ?? '') ?></a>
                                    <?php else: ?>
                                        <?= sanitize($item['title'] ?? '') ?>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
            
            <?php if (isset($pageTitle)): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0"><?= sanitize($pageTitle) ?></h1>
                    <?php if (isset($pageActions)): ?>
                        <div class="page-actions">
                            <?= $pageActions ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php endif; ?>
        </main>
        
        </div>

    <footer class="main-footer">
        &copy; <?= date('Y') ?> Comunica Play. Todos os direitos reservados.
    </footer>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/sortable.min.js"></script>
    <script src="/assets/js/custom.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inlineJS)): ?>
        <script>
            <?= $inlineJS ?>
        </script>
    <?php endif; ?>
</body>
</html>


