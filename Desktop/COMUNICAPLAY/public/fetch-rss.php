<?php
header('Content-Type: application/xml; charset=utf-8');

$url = $_GET['url'] ?? 'https://news.google.com/rss?hl=pt-BR&gl=BR&ceid=BR:pt-419'; // Exemplo: Google Notícias Brasil

if (filter_var($url, FILTER_VALIDATE_URL)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200 && $output) {
        echo $output;
    } else {
        http_response_code(500);
        echo '<error>Failed to fetch RSS feed. HTTP Status: ' . $httpcode . '</error>';
    }
} else {
    http_response_code(400);
    echo '<error>Invalid URL provided.</error>';
}
?>
