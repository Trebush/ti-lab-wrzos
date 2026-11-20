BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS uslugi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nazwa TEXT NOT NULL,
    cena INTEGER NOT NULL,
    popularnosc INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS uzytkownicy (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT NOT NULL UNIQUE,
    haslo_hash TEXT NOT NULL
);

INSERT INTO uslugi (id, nazwa, cena, popularnosc) VALUES
    (1, 'Regulacja hamulców', 50, 35),
    (2, 'Przegląd roweru', 120, 80),
    (3, 'Serwis napędu', 90, 50),
    (4, 'Centrowanie koła', 60, 20)
ON CONFLICT(id) DO UPDATE SET popularnosc = excluded.popularnosc
WHERE uslugi.popularnosc = 0 AND uslugi.nazwa = excluded.nazwa;

COMMIT;
