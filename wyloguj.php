<?php
require __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

$token = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(403);
    exit('Nieprawidłowe żądanie.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametry = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parametry['path'],
        'domain' => $parametry['domain'],
        'secure' => $parametry['secure'],
        'httponly' => $parametry['httponly'],
        'samesite' => $parametry['samesite']
    ]);
}
session_destroy();
header('Location: login.php');
exit;
