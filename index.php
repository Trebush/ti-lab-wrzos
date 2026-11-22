<?php
$firma = 'Szprycha';
$rok = date('Y');
$imie = $_GET['imie'] ?? '';
$imie = is_string($imie) ? trim($imie) : '';
$imie = $imie !== '' ? $imie : 'Gościu';
require __DIR__ . '/baza.php';
$max = $_GET['max'] ?? '';
$bladFiltra = '';
$limitCeny = null;
if ($max !== '') {
    $limitCeny = is_string($max)
        ? filter_var($max, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])
        : false;
    if ($limitCeny === false) {
        $bladFiltra = 'Maksymalna cena musi być nieujemną liczbą całkowitą.';
    }
}
if ($limitCeny !== null && $limitCeny !== false) {
    $zapytanie = $pdo->prepare('SELECT id, nazwa, cena, popularnosc FROM uslugi WHERE cena <= :max ORDER BY cena');
    $zapytanie->execute(['max' => $limitCeny]);
    $cennik = $zapytanie->fetchAll();
} else {
    $cennik = $pdo->query('SELECT id, nazwa, cena, popularnosc FROM uslugi ORDER BY id')->fetchAll();
}
$adresBezFiltra = 'index.php' . (is_string($_GET['imie'] ?? null) ? '?imie=' . rawurlencode($_GET['imie']) : '') . '#cennik';
$maksymalnaPopularnosc = 0;
foreach ($cennik as $usluga) {
    $maksymalnaPopularnosc = max($maksymalnaPopularnosc, $usluga['popularnosc']);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szprycha - serwis rowerowy</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>

    <header class="site-header">
        <a class="logo" href="#" aria-label="Szprycha — początek strony">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 64" width="72" height="38" role="img" aria-labelledby="logo-tytul" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <title id="logo-tytul">Szprycha</title>
                <circle cx="28" cy="42" r="18" />
                <circle cx="92" cy="42" r="18" />
                <polyline points="28,42 44,18 60,42 28,42" />
                <polyline points="44,18 82,18 60,42" />
                <polyline points="92,42 80,10 92,10" />
                <polyline points="38,14 50,14" />
                <polyline points="44,14 44,18" />
            </svg>
            <span>Szprycha</span>
        </a>

        <button class="menu-toggle" type="button" aria-controls="menu" aria-expanded="false">Menu</button>
        <nav class="menu" id="menu">
            <a href="#uslugi">Usługi</a>
            <a href="#cennik">Cennik</a>
            <a href="#kontakt">Kontakt</a>
        </nav>
    </header>

    <main>
        <section id="uslugi">
            <h1>Serwis rowerowy Szprycha</h1>
            <p class="greeting">Cześć, <?= htmlspecialchars($imie, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!</p>
            <h2>Nasze usługi</h2>

            <div class="cards">
                <article class="card">
                    <h3>Regulacja hamulców</h3>
                    <p>Regulacja i sprawdzenie hamulców rowerowych.</p>
                    <p class="card__price">od 50 zł</p>
                </article>

                <article class="card card--highlight">
                    <p class="card__badge">Najczęściej wybierane</p>
                    <h3>Przegląd roweru</h3>
                    <p>Kompleksowa kontrola i regulacja roweru.</p>
                    <p class="card__price">od 120 zł</p>
                </article>

                <article class="card">
                    <h3>Serwis napędu</h3>
                    <p>Czyszczenie i regulacja układu napędowego.</p>
                    <p class="card__price">od 90 zł</p>
                </article>
            </div>
        </section>

        <section id="cennik">
            <h2>Cennik</h2>
            <form class="price-filter" method="get" action="index.php#cennik">
                <?php if (is_string($_GET['imie'] ?? null)): ?>
                <input type="hidden" name="imie" value="<?= htmlspecialchars($_GET['imie'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <?php endif; ?>
                <label for="max">Maksymalna cena (zł)</label>
                <input id="max" name="max" type="number" min="0" step="1" value="<?= htmlspecialchars(is_string($max) ? $max : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"<?= $bladFiltra !== '' ? ' aria-invalid="true" aria-describedby="blad-filtra"' : '' ?>>
                <button type="submit">Filtruj</button>
                <a href="<?= htmlspecialchars($adresBezFiltra, ENT_QUOTES, 'UTF-8') ?>">Wyczyść filtr</a>
            </form>
            <?php if ($bladFiltra !== ''): ?>
            <p id="blad-filtra" role="alert"><?= htmlspecialchars($bladFiltra, ENT_QUOTES, 'UTF-8') ?> Wyświetlono wszystkie usługi.</p>
            <?php endif; ?>
            <?php if ($cennik === []): ?>
            <p id="brak-uslug">Brak usług spełniających podane kryteria.</p>
            <?php endif; ?>
            <p id="status" role="status" aria-live="polite" data-max="<?= htmlspecialchars(is_string($max) ? $max : 'nieprawidlowy', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></p>
            <table class="price-table">
                <thead>
                    <tr>
                        <th scope="col">Usługa</th>
                        <th scope="col" class="num">Cena</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cennik as $usluga): ?>
                    <tr>
                        <td><?= htmlspecialchars($usluga['nazwa']) ?></td>
                        <td class="num">od <?= $usluga['cena'] ?> zł</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <figure class="price-chart">
                <figcaption>Popularność usług</figcaption>
                <p>Wykres obejmuje usługi widoczne w cenniku.</p>
                <p id="zerowa-popularnosc"<?= $cennik !== [] && $maksymalnaPopularnosc == 0 ? '' : ' hidden' ?>>Brak danych o popularności — wszystkie wartości wynoszą 0.</p>
                <svg id="wykres" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 <?= max(1, count($cennik)) * 76 ?>" role="img" aria-labelledby="wykres-tytul wykres-opis">
                    <title id="wykres-tytul">Popularność usług Szprychy</title>
                    <desc id="wykres-opis">Długości zielonych słupków odpowiadają popularności usług widocznych w cenniku. Najdłuższy słupek oznacza największą popularność. Przy każdej usłudze podano jej wartość.</desc>
                    <?php foreach ($cennik as $indeks => $usluga): ?>
                    <g transform="translate(0 <?= $indeks * 76 ?>)">
                        <text x="0" y="20"><?= htmlspecialchars($usluga['nazwa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></text>
                        <rect x="0" y="32" width="<?= max(0, $usluga['popularnosc']) / max(1, $maksymalnaPopularnosc) * 200 ?>" height="24" rx="3" />
                        <text x="210" y="49"><?= (int) $usluga['popularnosc'] ?></text>
                    </g>
                    <?php endforeach; ?>
                </svg>
            </figure>
        </section>
    </main>

    <footer id="kontakt">
        <h2>Kontakt</h2>
        <p>Pracownia Rowerowa Szprycha</p>
        <p>Bydgoszcz</p>
        <p>Telefon: <a href="tel:+48500100200">500 100 200</a></p>
        <p>E-mail: <a href="mailto:kontakt@szprycha.example">kontakt@szprycha.example</a></p>
        <p>&copy; <?= $rok ?> <?= $firma ?></p>
    </footer>

</body>
</html>
