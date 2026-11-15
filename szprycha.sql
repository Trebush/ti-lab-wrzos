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

INSERT INTO uslugi (id, nazwa, cena)
SELECT 1, 'Regulacja hamulców', 50 WHERE NOT EXISTS (SELECT 1 FROM uslugi WHERE id = 1);
INSERT INTO uslugi (id, nazwa, cena)
SELECT 2, 'Przegląd roweru', 120 WHERE NOT EXISTS (SELECT 1 FROM uslugi WHERE id = 2);
INSERT INTO uslugi (id, nazwa, cena)
SELECT 3, 'Serwis napędu', 90 WHERE NOT EXISTS (SELECT 1 FROM uslugi WHERE id = 3);
INSERT INTO uslugi (id, nazwa, cena)
SELECT 4, 'Centrowanie koła', 60 WHERE NOT EXISTS (SELECT 1 FROM uslugi WHERE id = 4);

COMMIT;
