import './bootstrap';

const menuToggle = document.querySelector('[data-menu-toggle]');
const sidebar = document.querySelector('#sidebar');
const searchInput = document.querySelector('.search-box input[type="search"]');
const searchBox = document.querySelector('.search-box');
const passwordInput = document.querySelector('#password');
const passwordToggle = document.querySelector('[data-password-toggle]');

passwordToggle?.addEventListener('click', () => {
    if (!(passwordInput instanceof HTMLInputElement)) {
        return;
    }

    const showPassword = passwordInput.type === 'password';
    passwordInput.type = showPassword ? 'text' : 'password';
    passwordToggle.setAttribute('aria-pressed', String(showPassword));
    passwordToggle.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    passwordToggle.textContent = showPassword ? 'Sembunyikan' : 'Tampilkan';
});

document.querySelector('[data-search-toggle]')?.addEventListener('click', () => {
    searchBox?.classList.add('search-open');
    searchInput?.focus();
});

menuToggle?.addEventListener('click', () => {
    const isOpen = sidebar?.classList.toggle('is-open') ?? false;
    menuToggle.setAttribute('aria-expanded', String(isOpen));
});

document.addEventListener('click', (event) => {
    if (!sidebar?.classList.contains('is-open')) {
        return;
    }

    if (event.target instanceof Node && !sidebar.contains(event.target) && !menuToggle?.contains(event.target)) {
        sidebar.classList.remove('is-open');
        menuToggle?.setAttribute('aria-expanded', 'false');
    }
});

document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        searchBox?.classList.add('search-open');
        searchInput?.focus();
    }
});

document.addEventListener('click', (event) => {
    if (event.target instanceof Node && !searchBox?.contains(event.target)) {
        searchBox?.classList.remove('search-open');
        searchInput?.blur();
    }
});
