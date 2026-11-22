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

async function wczytajUslugi() {
    const status = document.querySelector('#status');
    const tbody = document.querySelector('.price-table tbody');
    if (!status || !tbody || status.dataset.apiEnabled !== 'true') {
        return;
    }

    status.textContent = 'Wczytywanie cennika…';
    try {
        const odpowiedz = await fetch('api.php');
        if (!odpowiedz.ok) {
            throw new Error('Błąd odpowiedzi API.');
        }
        const uslugi = await odpowiedz.json();
        if (!Array.isArray(uslugi)) {
            throw new Error('Nieprawidłowy format cennika.');
        }

        pokazTabele(uslugi);
        document.querySelector('#brak-uslug')?.remove();
        status.textContent = uslugi.length === 0 ? 'Brak usług w cenniku.' : 'Cennik wczytany.';
    } catch (blad) {
        status.textContent = 'Nie udało się wczytać cennika.';
    }
}

wczytajUslugi();
