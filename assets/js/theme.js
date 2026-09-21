function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    document.querySelectorAll('.theme-toggle').forEach(btn => {
        const icon = btn.querySelector('img');

        if (theme === 'dark') {
            icon.src = 'assets/img/light-mode.png';
            icon.alt = 'Light mode';
            btn.setAttribute('aria-label', 'Switch to light mode');
        } else {
            icon.src = 'assets/img/dark-mode.png';
            icon.alt = 'Dark mode';
            btn.setAttribute('aria-label', 'Switch to dark mode');
        }
    });
}

function toggleTheme() {
    const current =
        document.documentElement.getAttribute('data-theme') || 'light';

    applyTheme(current === 'dark' ? 'light' : 'dark');
}

document.addEventListener('DOMContentLoaded', () => {
    const current =
        document.documentElement.getAttribute('data-theme') || 'light';

    applyTheme(current);

    document.querySelectorAll('.theme-toggle').forEach(btn => {
        btn.addEventListener('click', toggleTheme);
    });
});