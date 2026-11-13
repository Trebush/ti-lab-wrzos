const menuToggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('.menu');

menuToggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('menu--open');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
});
