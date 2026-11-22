const menuToggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('.menu');

menuToggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('menu--open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
});

function pokazTabele(uslugi) {
    const tbody = document.querySelector('.price-table tbody');
    const fragment = document.createDocumentFragment();

    for (const usluga of uslugi) {
        if (!usluga || typeof usluga.nazwa !== 'string' || !Number.isFinite(usluga.cena)) {
            throw new Error('Nieprawidłowe dane usługi.');
        }
        const wiersz = document.createElement('tr');
        const nazwa = document.createElement('td');
        const cena = document.createElement('td');

        nazwa.textContent = usluga.nazwa;
        cena.textContent = `od ${usluga.cena} zł`;
        cena.classList.add('num');
        wiersz.append(nazwa, cena);
        fragment.append(wiersz);
    }

    tbody.replaceChildren(fragment);
}

function rysujWykres(uslugi) {
    const wykres = document.querySelector('#wykres');
    const ns = 'http://www.w3.org/2000/svg';
    const fragment = document.createDocumentFragment();
    const tytul = document.createElementNS(ns, 'title');
    const opis = document.createElementNS(ns, 'desc');
    tytul.id = 'wykres-tytul';
    tytul.textContent = 'Popularność usług Szprychy';
    opis.id = 'wykres-opis';
    opis.textContent = uslugi.length === 0
        ? 'Brak usług spełniających podane kryteria.'
        : 'Długości zielonych słupków odpowiadają popularności usług widocznych w cenniku. Przy każdej usłudze podano jej wartość.';
    fragment.append(tytul, opis);
    const maksimum = uslugi.reduce((wynik, usluga) => Math.max(wynik, usluga.popularnosc), 0);

    uslugi.forEach((usluga, indeks) => {
        const grupa = document.createElementNS(ns, 'g');
        grupa.setAttribute('transform', `translate(0 ${indeks * 76})`);
        const nazwa = document.createElementNS(ns, 'text');
        nazwa.setAttribute('x', '0');
        nazwa.setAttribute('y', '20');
        nazwa.textContent = usluga.nazwa;
        const slupek = document.createElementNS(ns, 'rect');
        slupek.setAttribute('x', '0');
        slupek.setAttribute('y', '32');
        slupek.setAttribute('width', String(Math.max(0, usluga.popularnosc) / Math.max(1, maksimum) * 200));
        slupek.setAttribute('height', '24');
        slupek.setAttribute('rx', '3');
        const popularnosc = document.createElementNS(ns, 'text');
        popularnosc.setAttribute('x', '210');
        popularnosc.setAttribute('y', '49');
        popularnosc.textContent = String(usluga.popularnosc);
        grupa.append(nazwa, slupek, popularnosc);
        fragment.append(grupa);
    });

    wykres.setAttribute('viewBox', `0 0 300 ${Math.max(1, uslugi.length) * 76}`);
    wykres.replaceChildren(fragment);
    document.querySelector('#zerowa-popularnosc').hidden = uslugi.length === 0 || maksimum !== 0;
}

let aktualneZapytanie = 0;
let opoznienie;

async function wczytajUslugi(max = '') {
    const status = document.querySelector('#status');
    const tbody = document.querySelector('.price-table tbody');
    if (!status || !tbody || !document.querySelector('#wykres')) {
        return;
    }

    const numerZapytania = ++aktualneZapytanie;
    const adres = max === '' ? 'api.php' : 'api.php?max=' + encodeURIComponent(max);
    status.textContent = 'Wczytywanie cennika…';
    try {
        const odpowiedz = await fetch(adres);
        if (!odpowiedz.ok) {
            throw new Error('Błąd odpowiedzi API.');
        }
        const uslugi = await odpowiedz.json();
        if (!Array.isArray(uslugi) || !uslugi.every(usluga => usluga
            && typeof usluga.nazwa === 'string'
            && Number.isFinite(usluga.cena)
            && Number.isFinite(usluga.popularnosc))) {
            throw new Error('Nieprawidłowy format cennika.');
        }
        if (numerZapytania !== aktualneZapytanie) {
            return;
        }

        pokazTabele(uslugi);
        rysujWykres(uslugi);
        document.querySelector('#brak-uslug')?.remove();
        document.querySelector('#blad-filtra')?.remove();
        const poleMax = document.querySelector('#max');
        poleMax.removeAttribute('aria-invalid');
        poleMax.removeAttribute('aria-describedby');
        const url = new URL(window.location.href);
        if (max === '') {
            url.searchParams.delete('max');
        } else {
            url.searchParams.set('max', max);
        }
        window.history.replaceState(null, '', url);
        status.textContent = uslugi.length === 0 ? 'Brak usług w cenniku.' : 'Cennik wczytany.';
    } catch (blad) {
        if (numerZapytania === aktualneZapytanie) {
            status.textContent = 'Nie udało się wczytać cennika.';
        }
    }
}

const filtr = document.querySelector('.price-filter');
const poleMax = document.querySelector('#max');
const statusCennika = document.querySelector('#status');
if (filtr && poleMax && statusCennika) {
    poleMax.addEventListener('input', () => {
        clearTimeout(opoznienie);
        aktualneZapytanie++;
        opoznienie = setTimeout(() => wczytajUslugi(poleMax.value), 300);
    });
    filtr.addEventListener('submit', zdarzenie => {
        zdarzenie.preventDefault();
        clearTimeout(opoznienie);
        wczytajUslugi(poleMax.value);
    });
    filtr.querySelector('a').addEventListener('click', zdarzenie => {
        zdarzenie.preventDefault();
        clearTimeout(opoznienie);
        poleMax.value = '';
        wczytajUslugi();
    });
    wczytajUslugi(statusCennika.dataset.max);
}
