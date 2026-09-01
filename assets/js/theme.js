function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    document.querySelectorAll('.theme-toggle').forEach(btn => {
        btn.textContent = theme === 'dark' ? '☀️' : '🌙';
        btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    });
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
}

document.addEventListener('DOMContentLoaded', () => {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(current);
    document.querySelectorAll('.theme-toggle').forEach(btn => {
        btn.addEventListener('click', toggleTheme);
    });
});