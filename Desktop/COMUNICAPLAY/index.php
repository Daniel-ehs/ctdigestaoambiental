<?php
/**
 * Script de Redirecionamento
 *
 * Este script redireciona permanentemente qualquer visitante
 * do index.php para a página de login especificada.
 */

// 1. Defina a URL de destino
$url_de_destino = 'https://comunica.danielsantos.eng.br/public/login.php';

// 2. Envia o cabeçalho de redirecionamento para o navegador
// O código de status 301 indica que o redirecionamento é permanente.
header('Location: ' . $url_de_destino, true, 301);

// 3. Garante que o script pare de ser executado imediatamente após o header
// Isso é uma boa prática para evitar que código adicional seja executado.
exit();

?>