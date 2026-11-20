<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/baza.php';

$wartoscId = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
$id = is_string($wartoscId)
    ? filter_var($wartoscId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;
$blad = '';
$usluga = false;
if ($id === false) {
    http_response_code(400);
    $blad = 'Nieprawidłowy identyfikator usługi.';
} else {
    $zapytanie = $pdo->prepare('SELECT id, nazwa, cena FROM uslugi WHERE id = :id');
    $zapytanie->execute(['id' => $id]);
    $usluga = $zapytanie->fetch();
    if (!$usluga) {
        http_response_code(404);
        $blad = 'Nie znaleziono wskazanej usługi.';
    }
}

if ($usluga) {
    $nazwa = $usluga['nazwa'];
    $cena = (string) $usluga['cena'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nazwa = is_string($_POST['nazwa'] ?? null) ? trim($_POST['nazwa']) : '';
        $cena = is_string($_POST['cena'] ?? null) ? $_POST['cena'] : '';
        $cenaLiczbowa = filter_var($cena, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!csrf_poprawny()) {
            http_response_code(403);
            $blad = 'Nieprawidłowe żądanie. Spróbuj ponownie.';
        } elseif ($nazwa === '' || !mb_check_encoding($nazwa, 'UTF-8') || mb_strlen($nazwa, 'UTF-8') > 100) {
            $blad = 'Nazwa usługi musi zawierać od 1 do 100 znaków.';
        } elseif ($cenaLiczbowa === false) {
            $blad = 'Cena musi być liczbą całkowitą większą od zera.';
        } else {
            $zapytanie = $pdo->prepare('UPDATE uslugi SET nazwa = :nazwa, cena = :cena WHERE id = :id');
            $zapytanie->execute(['nazwa' => $nazwa, 'cena' => $cenaLiczbowa, 'id' => $id]);
            header('Location: admin.php?ok=edytowano');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edycja usługi — Szprycha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <main class="container py-4">
        <div class="panel p-4">
            <h1>Edycja usługi</h1>
            <?php if ($blad !== ''): ?>
            <p class="alert alert-danger" role="alert"><?= htmlspecialchars($blad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($usluga): ?>
            <form method="post" action="edytuj.php" class="panel-form mb-3">
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <label class="form-label" for="nazwa">Nazwa usługi</label>
                <input class="form-control" id="nazwa" name="nazwa" required maxlength="100" value="<?= htmlspecialchars($nazwa, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <label class="form-label" for="cena">Cena (zł)</label>
                <input class="form-control" id="cena" name="cena" type="number" min="1" step="1" required value="<?= htmlspecialchars($cena, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button class="btn btn-primary" type="submit">Zapisz zmiany</button>
            </form>
            <?php endif; ?>
            <a class="btn btn-outline-secondary" href="admin.php">Powrót do panelu</a>
        </div>
    </main>
</body>
</html>
