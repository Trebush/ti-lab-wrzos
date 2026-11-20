<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/baza.php';

$blad = '';
$nazwa = '';
$cena = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa = is_string($_POST['nazwa'] ?? null) ? trim($_POST['nazwa']) : '';
    $cena = is_string($_POST['cena'] ?? null) ? $_POST['cena'] : '';
    $cenaLiczbowa = filter_var($cena, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $akcja = $_POST['akcja'] ?? '';
    if (!csrf_poprawny()) {
        http_response_code(403);
        $blad = 'Nieprawidłowe żądanie. Spróbuj ponownie.';
    } elseif ($akcja === 'usun') {
        $id = is_string($_POST['id'] ?? null)
            ? filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        if ($id === false) {
            $blad = 'Identyfikator usługi musi być liczbą całkowitą większą od zera.';
        } else {
            $zapytanie = $pdo->prepare('DELETE FROM uslugi WHERE id = :id');
            $zapytanie->execute(['id' => $id]);
            if ($zapytanie->rowCount() === 1) {
                header('Location: admin.php?ok=usunieto');
                exit;
            }
            $blad = 'Nie znaleziono wskazanej usługi.';
        }
    } elseif ($akcja !== 'dodaj') {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <nav class="navbar navbar-dark" aria-label="Nawigacja panelu">
        <div class="container gap-3">
            <a class="navbar-brand" href="index.php">Szprycha</a>
            <form method="post" action="wyloguj.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-outline-light">Wyloguj</button>
            </form>
        </div>
    </nav>
    <main class="container py-4">
        <h1>Panel administratora</h1>
        <p>Witaj, <?= htmlspecialchars($_SESSION['uzytkownik'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php if ($blad !== ''): ?>
        <p class="alert alert-danger" role="alert"><?= htmlspecialchars($blad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php elseif (($_GET['ok'] ?? '') === 'dodano'): ?>
        <p class="alert alert-success" role="status">Usługa dodana.</p>
        <?php elseif (($_GET['ok'] ?? '') === 'usunieto'): ?>
        <p class="alert alert-success" role="status">Usługa usunięta.</p>
        <?php elseif (($_GET['ok'] ?? '') === 'edytowano'): ?>
        <p class="alert alert-success" role="status">Usługa zaktualizowana.</p>
        <?php endif; ?>
        <div class="row g-4">
        <section class="col-lg-8" aria-labelledby="lista-uslug">
            <h2 id="lista-uslug">Usługi</h2>
            <?php if ($uslugi === []): ?>
            <p class="alert alert-info">Brak usług w bazie.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-striped align-middle service-table">
                <thead>
                    <tr><th scope="col">Nazwa usługi</th><th scope="col" class="text-end">Cena</th><th scope="col">Akcje</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($uslugi as $usluga): ?>
                    <tr>
                        <td><?= htmlspecialchars($usluga['nazwa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="text-end text-nowrap"><?= htmlspecialchars((string) $usluga['cena'], ENT_QUOTES, 'UTF-8') ?> zł</td>
                        <td>
                            <a class="btn btn-sm btn-outline-secondary" href="edytuj.php?id=<?= (int) $usluga['id'] ?>" aria-label="Edytuj usługę: <?= htmlspecialchars($usluga['nazwa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Edytuj</a>
                            <form method="post" action="admin.php" class="d-inline">
                                <input type="hidden" name="akcja" value="usun">
                                <input type="hidden" name="id" value="<?= (int) $usluga['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Usuń usługę: <?= htmlspecialchars($usluga['nazwa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Usuń</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>
        <section class="col-lg-4" aria-labelledby="dodaj-usluge">
            <h2 id="dodaj-usluge">Dodaj usługę</h2>
            <form method="post" action="admin.php" class="panel-form">
                <input type="hidden" name="akcja" value="dodaj">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <label class="form-label" for="nazwa">Nazwa usługi</label>
                <input class="form-control" id="nazwa" name="nazwa" required maxlength="100" value="<?= htmlspecialchars($nazwa, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <label class="form-label" for="cena">Cena (zł)</label>
                <input class="form-control" id="cena" name="cena" type="number" required min="1" step="1" value="<?= htmlspecialchars($cena, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button type="submit" class="btn btn-primary">Dodaj</button>
            </form>
        </section>
        </div>
    </main>
</body>
</html>
