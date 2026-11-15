<?php
require __DIR__ . '/auth.php';
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administratora — Szprycha</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <main class="panel">
        <h1>Panel administratora</h1>
        <p>Witaj, <?= htmlspecialchars($_SESSION['uzytkownik'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <form method="post" action="wyloguj.php">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Wyloguj</button>
        </form>
    </main>
</body>
</html>
