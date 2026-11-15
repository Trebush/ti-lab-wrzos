<?php
session_start();
if (isset($_SESSION['uzytkownik'])) {
    header('Location: admin.php');
    exit;
}

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$blad = '';
$login = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = is_string($_POST['login'] ?? null) ? trim($_POST['login']) : '';
    $haslo = is_string($_POST['haslo'] ?? null) ? $_POST['haslo'] : '';
    $token = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
    if (hash_equals($_SESSION['csrf'], $token)) {
        require __DIR__ . '/baza.php';
        $zapytanie = $pdo->prepare('SELECT login, haslo_hash FROM uzytkownicy WHERE login = :login');
        $zapytanie->execute(['login' => $login]);
        $uzytkownik = $zapytanie->fetch();
        if ($uzytkownik && password_verify($haslo, $uzytkownik['haslo_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uzytkownik'] = $uzytkownik['login'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: admin.php');
            exit;
        }
    }
    unset($haslo);
    $blad = 'Błędny login lub hasło.';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie — Szprycha</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <main class="panel">
        <h1>Logowanie administratora</h1>
        <?php if ($blad !== ''): ?>
        <p role="alert"><?= htmlspecialchars($blad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <label for="login">Login</label>
            <input id="login" name="login" autocomplete="username" required value="<?= htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <label for="haslo">Hasło</label>
            <input id="haslo" name="haslo" type="password" autocomplete="current-password" required>
            <button type="submit">Zaloguj</button>
        </form>
    </main>
</body>
</html>
