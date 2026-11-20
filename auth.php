<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['uzytkownik'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/csrf.php';
