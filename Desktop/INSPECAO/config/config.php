<?php
/**
 * Configurações gerais do sistema
 */

// Evitar inclusão múltipla
if (defined("CONFIG_LOADED")) {
    return;
}
define("CONFIG_LOADED", true);

// Informações do site
define("SITE_NAME", "Sistema de Inspeções de Segurança");
// Tentar detectar a URL base dinamicamente ou usar um valor padrão
$protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || $_SERVER["SERVER_PORT"] == 443 ? "https://" : "http://";
$host = $_SERVER["HTTP_HOST"] ?? "localhost"; // Usar localhost como fallback
$script_dir = dirname($_SERVER["SCRIPT_NAME"]);
// Remover /public ou similar se estiver presente e não for a raiz
// Remover /public ou similar se estiver presente e não for a raiz
// Esta lógica pode precisar de ajuste dependendo da configuração do servidor web
// Por exemplo, se o CapRover serve diretamente do diretório raiz do projeto, $script_dir já estará correto.
// Se o CapRover serve de um subdiretório como 'public', a lógica abaixo pode ser necessária.
// Para simplificar e garantir que a URL base seja a raiz da aplicação, vamos tentar uma abordagem mais robusta.
// Se a aplicação estiver em um subdiretório, o usuário precisará ajustar manualmente ou configurar o servidor web para reescrever URLs.
// Por enquanto, vamos assumir que a aplicação é servida da raiz do domínio/subdomínio.
$script_dir = "/"; // Assume que a aplicação está na raiz do domínio/subdomínio
$base_url = rtrim($protocol . $host . $script_dir, "/");
define("SITE_URL", $base_url);

// Configurações de banco de dados
define("DB_HOST", getenv('DB_HOST'));
define("DB_NAME", getenv('DB_NAME'));
define("DB_USER", getenv('DB_USER'));
define("DB_PASS", getenv('DB_PASSWORD'));
define("DB_CHARSET", "utf8mb4");

// Configurações de diretórios
// __DIR__ aponta para o diretório do arquivo atual (config)
// dirname(__DIR__) aponta para o diretório pai (raiz do projeto)
if (!defined("BASE_PATH")) {
    define("BASE_PATH", dirname(__DIR__));
}
if (!defined("UPLOAD_DIR")) {
    define("UPLOAD_DIR", BASE_PATH . "/uploads");
}

// Definir constantes de DIRETÓRIO apenas se não existirem
if (!defined("FOTOS_ANTES_DIR")) {
    define("FOTOS_ANTES_DIR", UPLOAD_DIR . "/fotos_antes");
}
if (!defined("FOTOS_DEPOIS_DIR")) {
    define("FOTOS_DEPOIS_DIR", UPLOAD_DIR . "/fotos_depois");
}
if (!defined("PDFS_DIR")) {
    define("PDFS_DIR", UPLOAD_DIR . "/pdfs");
}

// *** CORREÇÃO: Definir constantes de URL para as fotos ***
$relative_upload_path = str_replace(BASE_PATH, "", UPLOAD_DIR);
$relative_upload_path = ltrim($relative_upload_path, "/"); // Garantir que não comece com /

if (!defined("FOTOS_ANTES_URL")) {
    // Monta a URL baseando-se na SITE_URL e no caminho relativo
    define("FOTOS_ANTES_URL", SITE_URL . "/" . $relative_upload_path . "/fotos_antes");
}
if (!defined("FOTOS_DEPOIS_URL")) {
    define("FOTOS_DEPOIS_URL", SITE_URL . "/" . $relative_upload_path . "/fotos_depois");
}

// Configurações de sessão
define("SESSION_NAME", "inspecao_seguranca_session");
define("SESSION_LIFETIME", 7200); // 2 horas

// Configurações de segurança
define("HASH_COST", 10); // Custo do bcrypt

// Configurações de data e hora
date_default_timezone_set("America/Sao_Paulo");
setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "portuguese");

// Configurações de erro
ini_set("display_errors", 1);
error_reporting(E_ALL);
ini_set("log_errors", 1);
// Garantir que o diretório de logs exista
$logDir = BASE_PATH . "/logs";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set("error_log", $logDir . "/error.log");

// Configurações de upload
define("MAX_UPLOAD_SIZE", 20 * 1024 * 1024); // 20MB
define("ALLOWED_EXTENSIONS", ["jpg", "jpeg", "png", "gif", "webp"]);

// Versão do sistema
define("SYSTEM_VERSION", "1.0.0");

?>
