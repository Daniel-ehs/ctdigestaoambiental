<?php
/**
 * Inicialização do sistema
 * Sistema de Gerenciamento de Inspeções de Segurança
 */

// Iniciar sessão
session_start();

// Definir constantes
define("BASE_PATH", __DIR__);
define("UPLOADS_DIR", BASE_PATH . "/uploads");

// Incluir arquivos de configuração e helpers
require_once "config/config.php";
require_once "config/database.php"; // A classe Database é definida aqui
require_once "utils/helpers.php";

// Criar diretórios de upload se não existirem
if (defined("UPLOAD_DIR") && !file_exists(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        error_log("Falha ao criar o diretório de uploads: " . UPLOAD_DIR);
    }
}
if (!file_exists(UPLOADS_DIR . '/fotos_antes')) {
    mkdir(UPLOADS_DIR . '/fotos_antes', 0755, true);
}
if (!file_exists(UPLOADS_DIR . '/fotos_depois')) {
    mkdir(UPLOADS_DIR . '/fotos_depois', 0755, true);
}
if (!file_exists(UPLOADS_DIR . '/fotos_pendentes')) {
    mkdir(UPLOADS_DIR . '/fotos_pendentes', 0755, true);
}
if (defined("PDFS_DIR") && !file_exists(PDFS_DIR)) {
    if (!mkdir(PDFS_DIR, 0755, true)) {
        error_log("Falha ao criar o diretório: " . PDFS_DIR);
    }
}

// Obter rota da URL
$route = $_GET["route"] ?? "login";
$action = $_GET["action"] ?? "index";
$id = $_GET["id"] ?? null;
$type = $_GET["type"] ?? null;

// Roteamento
switch ($route) {
    case "login":
        require_once "controllers/AuthController.php";
        $controller = new AuthController(); 
        $controller->login();
        break;
        
    case "logout":
        require_once "controllers/AuthController.php";
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case "auth":
        require_once "controllers/AuthController.php";
        $controller = new AuthController();
        
        switch ($action) {
            case "alterarSenha":
                $controller->alterarSenha();
                break;
                
            default:
                $controller->login();
        }
        break;
        
    case "dashboard":
        require_once "controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->index();
        break;

    case "painel":
        require_once "controllers/PainelController.php";
        $controller = new PainelController();
        
        switch ($action) {
            case "placa":
                $controller->placa();
                break;
            default:
                $controller->placa();
                break;
        }
        break;
        
    case "inspecoes":
        require_once "controllers/InspecaoController.php";
        $controller = new InspecaoController(); 
        
        switch ($action) {
            case "create":
                $controller->create();
                break;
            case "store":
                $controller->store();
                break;
            case "edit":
                $controller->edit($id);
                break;
            case "update":
                $controller->update($id);
                break;
            case "delete":
                $controller->delete($id);
                break;
            case "view":
                $controller->view($id);
                break;
            case "concluir": 
                $controller->showConcluirForm($id);
                break;
            case "processConcluir": 
                $controller->processConcluir(); 
                break;
            case "downloadExcel":
                $controller->downloadExcel();
                break;
            case "uploadExcel": 
                $controller->uploadApontamentos();
                break;
            case "gerarPdf":
                $controller->gerarPdf();
                break;
            default:
                $controller->index();
        }
        break;
        
    case "empresas":
        require_once "controllers/EmpresaController.php";
        $controller = new EmpresaController();
        
        switch ($action) {
            case "create":
                $controller->create();
                break;
            case "store":
                $controller->store();
                break;
            case "edit":
                $controller->edit($id);
                break;
            case "update":
                $controller->update($id);
                break;
            case "delete":
                $controller->delete($id);
                break;
            default:
                $controller->index();
        }
        break;
        
    case "cadastros":
        require_once "controllers/CadastrosController.php";
        $controller = new CadastrosController();
        
        switch ($action) {
            case "create":
                $controller->create($type);
                break;
            case "store":
                $controller->store($type);
                break;
            case "edit":
                $controller->edit($type, $id);
                break;
            case "update":
                $controller->update($type, $id);
                break;
            case "delete":
                $controller->delete($type, $id);
                break;
            default:
                $controller->index($type);
        }
        break;
        
    case "planos":
        require_once "controllers/PlanoAcaoController.php";
        $controller = new PlanoAcaoController();
        
        switch ($action) {
            case "create":
                $inspecaoId = $_GET["inspecao_id"] ?? null;
                if ($inspecaoId === null) {
                    setFlashMessage("error", "ID da inspeção não fornecido.");
                    redirect("index.php?route=inspecoes");
                }
                $controller->create($inspecaoId);
                break;
            case "store":
                $controller->store();
                break;
            case "pdf":
                 if ($id === null) {
                    setFlashMessage("error", "ID do plano de ação não fornecido para gerar PDF.");
                    redirect("index.php?route=planos");
                 }
                 $controller->generatePDF($id);
                 break;
            case "view":
                 if ($id === null) {
                    setFlashMessage("error", "ID do plano de ação não fornecido para visualização.");
                    redirect("index.php?route=planos");
                 }
                 $controller->view($id);
                 break;
            case "delete": 
                 if ($id === null) {
                    setFlashMessage("error", "ID do plano de ação não fornecido para exclusão.");
                    redirect("index.php?route=planos");
                 }
                 $controller->delete($id); 
                 break;
            default:
                $controller->index();
        }
        break;
        
    case "projetos":
        require_once "controllers/ProjetoController.php";
        $controller = new ProjetoController();
        
        switch ($action) {
            case "create":
                $controller->create();
                break;
            case "store":
                $controller->store();
                break;
            case "edit":
                $controller->edit($id);
                break;
            case "update":
                $controller->update($id);
                break;
            case "delete":
                $controller->delete($id);
                break;
            case "concluir":
                $controller->concluir($id);
                break;
            case "cancelar":
                $controller->cancelar($id);
                break;
            case "view":
                $controller->view($id);
                break;
            default:
                $controller->index();
        }
        break;
        
    case "relatorios":
        if (file_exists("controllers/RelatorioController.php")) {
             require_once "controllers/RelatorioController.php";
             $controller = new RelatorioController();
        
             switch ($action) {
                 case "semanal":
                      $controller->semanal();
                      break;
                 case "generate":
                      $controller->generate($_GET["type"] ?? null); 
                      break;
                 case "deleteRelatorio":
                      $controller->deleteRelatorio();
                      break;
                 default:
                      $controller->index();
             }
        } else {
             http_response_code(404);
             echo "Erro 404: Módulo de Relatórios não encontrado.";
             exit;
        }
        break;
        
    // --- INÍCIO DAS NOVAS ROTAS ---
    case "reportar":
        require_once "controllers/PublicoController.php";
        $controller = new PublicoController();
        
        switch ($action) {
            case "store":
                $controller->store();
                break;
            default:
                $controller->index();
        }
        break;

    case "aprovacao":
        require_once "controllers/AprovacaoController.php";
        $controller = new AprovacaoController();

        if (!isset($_SESSION["user_id"]) || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') { 
            setFlashMessage("error", "Acesso não autorizado.");
            redirect("index.php?route=login");
            exit;
        }
        
        switch ($action) {
            case "aprovar":
                $controller->aprovar($id);
                break;
            case "rejeitar":
                $controller->rejeitar($id);
                break;
            case "view":
                 $controller->view($id);
                 break;
            default:
                $controller->index();
        }
        break;
    // --- FIM DAS NOVAS ROTAS ---
        
    case "api":
        require_once "controllers/ApiController.php";
        $controller = new ApiController();
        
        switch ($action) {
            case "getSetoresPorEmpresa":
                $controller->getSetoresPorEmpresa($_GET["empresa_id"] ?? null);
                break;
            case "getLocaisPorSetor":
                $controller->getLocaisPorSetor($_GET["setor_id"] ?? null); 
                break;
            case "getEstatisticas":
                $controller->getEstatisticas();
                break;
            case "correctText":
                $controller->correctText();
                break;
            default:
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Endpoint não encontrado"]);
                exit;
        }
        break;
        
    default:
        if (!isset($_SESSION["user_id"])) {
            require_once "controllers/AuthController.php";
            $controller = new AuthController();
            $controller->login();
        } else {
            require_once "controllers/DashboardController.php";
            $controller = new DashboardController();
            $controller->index();
        }
}
