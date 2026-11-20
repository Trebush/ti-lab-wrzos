<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/baza.php';
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$blad = '';
$nazwa = '';
$cena = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa = is_string($_POST['nazwa'] ?? null) ? trim($_POST['nazwa']) : '';
    $cena = is_string($_POST['cena'] ?? null) ? $_POST['cena'] : '';
    $token = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
    $cenaLiczbowa = filter_var($cena, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (($_POST['akcja'] ?? '') !== 'dodaj' || !hash_equals($_SESSION['csrf'], $token)) {
        $blad = 'Nieprawidłowe żądanie. Spróbuj ponownie.';
    } elseif ($nazwa === '' || !mb_check_encoding($nazwa, 'UTF-8') || mb_strlen($nazwa, 'UTF-8') > 100) {
        $blad = 'Nazwa usługi musi zawierać od 1 do 100 znaków.';
    } elseif ($cenaLiczbowa === false) {
        $blad = 'Cena musi być liczbą całkowitą większą od zera.';
    } else {
        $zapytanie = $pdo->prepare('INSERT INTO uslugi (nazwa, cena) VALUES (:nazwa, :cena)');
        $zapytanie->execute(['nazwa' => $nazwa, 'cena' => $cenaLiczbowa]);
        header('Location: admin.php?ok=dodano');
        exit;
    }
}
$uslugi = $pdo->query('SELECT id, nazwa, cena FROM uslugi ORDER BY nazwa')->fetchAll();
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
        <?php if ($blad !== ''): ?>
        <p role="alert"><?= htmlspecialchars($blad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php elseif (($_GET['ok'] ?? '') === 'dodano'): ?>
        <p role="status">Usługa dodana.</p>
        <?php endif; ?>
        <section aria-labelledby="lista-uslug">
            <h2 id="lista-uslug">Usługi</h2>
            <?php if ($uslugi === []): ?>
            <p>Brak usług w bazie.</p>
            <?php else: ?>
            <table class="price-table">
                <thead>
                    <tr><th scope="col">Nazwa usługi</th><th scope="col" class="num">Cena</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($uslugi as $usluga): ?>
                    <tr>
                        <td><?= htmlspecialchars($usluga['nazwa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="num"><?= htmlspecialchars((string) $usluga['cena'], ENT_QUOTES, 'UTF-8') ?> zł</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
        <section aria-labelledby="dodaj-usluge">
            <h2 id="dodaj-usluge">Dodaj usługę</h2>
            <form method="post" action="admin.php">
                <input type="hidden" name="akcja" value="dodaj">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="nazwa">Nazwa usługi</label>
                <input id="nazwa" name="nazwa" required maxlength="100" value="<?= htmlspecialchars($nazwa, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <label for="cena">Cena (zł)</label>
                <input id="cena" name="cena" type="number" required min="1" step="1" value="<?= htmlspecialchars($cena, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button type="submit">Dodaj</button>
            </form>
        </section>
        <form method="post" action="wyloguj.php">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Wyloguj</button>
        </form>
    </main>
</body>
</html>
