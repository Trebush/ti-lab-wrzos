<?php
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

function csrf_poprawny(): bool
{
    $token = $_POST['csrf_token'] ?? null;
    return is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}
