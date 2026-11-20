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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <main class="container py-4">
        <div class="panel p-4">
        <h1>Logowanie administratora</h1>
        <?php if ($blad !== ''): ?>
        <p class="alert alert-danger" role="alert"><?= htmlspecialchars($blad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="login.php" class="panel-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="login">Login</label>
            <input class="form-control" id="login" name="login" autocomplete="username" required value="<?= htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <label class="form-label" for="haslo">Hasło</label>
            <input class="form-control" id="haslo" name="haslo" type="password" autocomplete="current-password" required>
            <button type="submit" class="btn btn-primary">Zaloguj</button>
        </form>
    </div>
    </main>
</body>
</html>
