<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Skrypt dostępny wyłącznie przez PHP CLI.');
}

try {
    require __DIR__ . '/baza.php';
    $sql = file_get_contents(__DIR__ . '/szprycha.sql');
    if ($sql === false) {
        throw new RuntimeException('Nie można odczytać szprycha.sql.');
    }
    $pdo->exec($sql);
    $zapytanie = $pdo->prepare('SELECT id FROM uzytkownicy WHERE login = :login');
    $zapytanie->execute(['login' => 'admin']);

    if (!$zapytanie->fetch()) {
        if (stream_isatty(STDIN)) {
            throw new RuntimeException('Przekaż hasło przez standardowe wejście z bezpiecznego monitu PowerShell.');
        }
        $haslo = fgets(STDIN);
        $haslo = $haslo === false ? '' : rtrim($haslo, "\r\n");
        if (trim($haslo) === '') {
            throw new RuntimeException('Hasło administratora nie może być puste.');
        }
        $hash = password_hash($haslo, PASSWORD_DEFAULT);
        unset($haslo);
        $zapytanie = $pdo->prepare('INSERT INTO uzytkownicy (login, haslo_hash) VALUES (:login, :hash)');
        $zapytanie->execute(['login' => 'admin', 'hash' => $hash]);
    }

    echo "Baza gotowa.\n";
} catch (Throwable $blad) {
    fwrite(STDERR, 'Błąd inicjalizacji: ' . $blad->getMessage() . PHP_EOL);
    exit(1);
}
