<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Inspeções</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" xintegrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous">
    
    <!-- Google Fonts - Nunito (mantido para consistência com o original) -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Fonte Poppins (adicionada para consistência com inspecoes_lista.php) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Inline CSS para garantir negrito e gradiente verde -->
    <style>
        /* Garantir título em negrito */
        .navbar-custom .navbar-brand {
            font-weight: 700 !important;
            font-family: 'Nunito', sans-serif;
            color: #ffffff !important; /* Branco para contraste com o gradiente */
        }

        /* Aplicar gradiente verde à navbar */
        .navbar-custom {
            background: linear-gradient(90deg, #28a745, #52c41a) !important;
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Ajustar links da navbar para contraste */
        .navbar-custom .nav-link,
        .navbar-custom .dropdown-toggle {
            color: #ffffff !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .dropdown-toggle:hover {
            color: #e6f4ea !important; /* Tom claro para hover */
        }

        /* Estilo do dropdown */
        .navbar-custom .dropdown-menu {
            background: #ffffff;
            border: 1px solid #28a745;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .dropdown-item {
            color: #333333;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .navbar-custom .dropdown-item:hover {
            background: #f0f4f0;
            color: #28a745;
        }

        /* Garantir fundo claro no body */
        body {
            background: #f5f6f5;
            font-family: 'Poppins', sans-serif;
        }

        /* Ajustar sidebar para consistência */
        .sidebar {
            background: #ffffff;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar .nav-link {
            color: #333333;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #f0f4f0;
            color: #28a745;
            border-radius: 8px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .navbar-custom .navbar-brand {
                font-size: 1.2rem;
            }
            .sidebar {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Partículas de fundo (alinhado com inspecoes_lista.php) -->
    <canvas id="particles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></canvas>

    <?php 
    // Define a variável $currentRoute com base no parâmetro GET, ou um valor padrão
    $currentRoute = $_GET["route"] ?? "login";
    $currentType = $_GET["type"] ?? null;
    
    if (isset($_SESSION["user_id"])): 
    ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?route=dashboard">
                <i class="fas fa-hard-hat me-1"></i> Gerenciamento de Inspeções
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION["user_nome"] ?? 'Usuário'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?route=auth&action=alterarSenha">
                                <i class="fas fa-key me-2"></i> Alterar Senha
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?route=logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentRoute === "dashboard" ? "active" : ""; ?>" href="index.php?route=dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentRoute === "inspecoes" ? "active" : ""; ?>" href="index.php?route=inspecoes">
                            <i class="fas fa-clipboard-check me-2"></i> Inspeções
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentRoute === "planos" ? "active" : ""; ?>" href="index.php?route=planos">
                            <i class="fas fa-tasks me-2"></i> Planos de Ação
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentRoute === "projetos" ? "active" : ""; ?>" href="index.php?route=projetos">
                            <i class="fas fa-project-diagram me-2"></i> Projetos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentRoute === "relatorios" ? "active" : ""; ?>" href="index.php?route=relatorios">
                            <i class="fas fa-chart-bar me-2"></i> Relatórios
                        </a>
                    </li>
                    <?php if (isset($_SESSION["user_nivel"]) && $_SESSION["user_nivel"] === "admin"): ?>
                    <!-- INÍCIO DA ALTERAÇÃO -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentRoute === "aprovacao" ? "active" : ""; ?>" href="index.php?route=aprovacao">
                            <i class="fas fa-user-check me-2"></i> Aprovações
                        </a>
                    </li>
                    <!-- FIM DA ALTERAÇÃO -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo $currentRoute === "cadastros" ? "active" : ""; ?>" 
                           href="#" id="cadastrosDropdown" role="button" data-bs-toggle="collapse" 
                           data-bs-target="#cadastrosSubmenu" aria-expanded="<?php echo $currentRoute === "cadastros" ? "true" : "false"; ?>">
                            <i class="fas fa-cog me-2"></i> Cadastros
                        </a>
                        <div class="collapse <?php echo $currentRoute === "cadastros" ? "show" : ""; ?>" id="cadastrosSubmenu">
                            <ul class="nav flex-column ms-3">
                                <?php if (isset($_SESSION["user_nivel"]) && $_SESSION["user_nivel"] === "admin"): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentRoute === "cadastros" && $currentType === "empresas" ? "active" : ""; ?>" 
                                       href="index.php?route=cadastros&type=empresas">
                                        <i class="fas fa-building me-2"></i> Empresas
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentRoute === "cadastros" && $currentType === "setores" ? "active" : ""; ?>" 
                                       href="index.php?route=cadastros&type=setores">
                                        <i class="fas fa-industry me-2"></i> Setores
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentRoute === "cadastros" && $currentType === "locais" ? "active" : ""; ?>" 
                                       href="index.php?route=cadastros&type=locais">
                                        <i class="fas fa-map-marker-alt me-2"></i> Locais
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentRoute === "cadastros" && $currentType === "tipos" ? "active" : ""; ?>" 
                                       href="index.php?route=cadastros&type=tipos">
                                        <i class="fas fa-tags me-2"></i> Tipos de Apontamento
                                    </a>
                                </li>
                                <?php if (isset($_SESSION["user_nivel"]) && $_SESSION["user_nivel"] === "admin"): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentRoute === "cadastros" && $currentType === "usuarios" ? "active" : ""; ?>" 
                                       href="index.php?route=cadastros&type=usuarios">
                                        <i class="fas fa-users me-2"></i> Usuários
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php 
                // Incluir a função getFlashMessage se ainda não estiver disponível
                if (!function_exists("getFlashMessage")) {
                    // Tentar incluir helpers.php se existir
                    if (file_exists("utils/helpers.php")) {
                        require_once "utils/helpers.php";
                    } else {
                        // Definir uma função dummy para evitar erro fatal
                        function getFlashMessage() { return null; }
                    }
                }
                if ($flashMessage = getFlashMessage()): 
                ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage["type"]); ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($flashMessage["message"]); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
    <?php endif; // Fim do if (isset($_SESSION["user_id"])) ?>


</body>
</html>
