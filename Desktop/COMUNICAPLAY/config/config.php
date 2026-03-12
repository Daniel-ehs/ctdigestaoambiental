<?php
ob_start();

// Configurações gerais do sistema
define("SITE_NAME", "Comunica Play");
define("SITE_URL", "https://comunica.danielsantos.eng.br");
define("UPLOAD_MAX_SIZE", 200 * 1024 * 1024); // 200MB
define("ALLOWED_VIDEO_TYPES", ["mp4", "avi", "mov"]);
define("ALLOWED_IMAGE_TYPES", ["jpg", "jpeg", "png", "gif"]);
define("HEARTBEAT_INTERVAL", 300); // 5 minutos em segundos
define("SESSION_TIMEOUT", 3600); // 1 hora em segundos

// Caminhos
define("UPLOAD_PATH", __DIR__ . "/../assets/uploads/");
define("VIDEO_PATH", UPLOAD_PATH . "videos/");
define("IMAGE_PATH", UPLOAD_PATH . "imagens/");
define("THUMBNAIL_PATH", UPLOAD_PATH . "miniaturas/");

// URLs
define("UPLOAD_URL", SITE_URL . "/assets/uploads/");
define("VIDEO_URL", UPLOAD_URL . "videos/");
define("IMAGE_URL", UPLOAD_URL . "imagens/");
define("THUMBNAIL_URL", UPLOAD_URL . "miniaturas/");

// Configurações de segurança
define("PASSWORD_MIN_LENGTH", 6);
define("MAX_LOGIN_ATTEMPTS", 5);
define("LOGIN_LOCKOUT_TIME", 900); // 15 minutos

// Configuração de ambiente (true para desenvolvimento, false para produção)
define("IS_DEVELOPMENT", true); // Mudar para false em produção

