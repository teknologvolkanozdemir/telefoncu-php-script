<?php
require __DIR__ . '/config.php';
session_start();
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return number_format((float)$n, 2, ',', '.') . ' TL'; }
function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('Geçersiz istek'); }
}
function header_html($title, $base = '') {
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($title) . '</title>'
       . '<style>body{font-family:sans-serif;max-width:900px;margin:0 auto;padding:10px}.grid{display:flex;flex-wrap:wrap;gap:15px}'
       . '.card{border:1px solid #ccc;padding:10px;width:200px}.card img{max-width:100%;height:150px;object-fit:contain}'
       . 'input,textarea,select{width:100%;padding:6px;margin:4px 0;box-sizing:border-box}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ccc;padding:5px}'
       . 'button,.btn{background:#0a58ca;color:#fff;border:0;padding:8px 14px;text-decoration:none;cursor:pointer}</style></head><body>'
       . '<h1><a href="' . $base . 'index.php" style="text-decoration:none;color:inherit">' . e(SITE_NAME) . '</a></h1><hr>';
}
