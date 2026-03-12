<?php

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_DATABASE') ?: 'dani7103_comunicaplay',
    'username' => getenv('DB_USERNAME') ?: 'dani7103_comunicaplay',
    'password' => getenv('DB_PASSWORD') ?: 'nova@2025',
    'charset' => 'utf8mb4' // Mantemos o charset para compatibilidade
];

?>

