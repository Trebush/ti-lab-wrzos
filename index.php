<?php
$firma = 'Szprycha';
$rok = date('Y');
$cennik = [
    ['nazwa' => 'Regulacja hamulców', 'cena' => 50],
    ['nazwa' => 'Przegląd roweru', 'cena' => 120],
    ['nazwa' => 'Serwis napędu', 'cena' => 90],
    ['nazwa' => 'Centrowanie koła', 'cena' => 60]
];
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
        <p class="logo">Szprycha</p>

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
