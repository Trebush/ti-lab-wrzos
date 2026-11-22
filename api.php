<?php
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function odpowiedz(int $kod, array $dane): void
{
    try {
        $json = json_encode($dane, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } catch (JsonException $blad) {
        $kod = 500;
        $json = '{"blad":"Nie udało się przygotować odpowiedzi."}';
    }
    http_response_code($kod);
    echo $json;
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    odpowiedz(405, ['blad' => 'Metoda niedozwolona.']);
}

$max = null;
if (array_key_exists('max', $_GET)) {
    $max = is_string($_GET['max'])
        ? filter_var($_GET['max'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])
        : false;
    if ($max === false) {
        odpowiedz(400, ['blad' => 'Maksymalna cena musi być nieujemną liczbą całkowitą.']);
    }
}

try {
    if (!is_file(__DIR__ . '/szprycha.sqlite')) {
        throw new RuntimeException('Brak bazy danych.');
    }
    require __DIR__ . '/baza.php';
    if ($max !== null) {
        $zapytanie = $pdo->prepare('SELECT id, nazwa, cena, popularnosc FROM uslugi WHERE cena <= :max ORDER BY cena');
        $zapytanie->execute(['max' => $max]);
    } else {
        $zapytanie = $pdo->query('SELECT id, nazwa, cena, popularnosc FROM uslugi ORDER BY cena');
    }
    $uslugi = $zapytanie->fetchAll();
    odpowiedz(200, $uslugi);
} catch (Throwable $blad) {
    odpowiedz(500, ['blad' => 'Nie udało się pobrać cennika.']);
}
