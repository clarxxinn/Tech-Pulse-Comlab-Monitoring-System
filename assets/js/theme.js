function applyTheme(theme) {
    const nextTheme = theme === 'dark' ? 'light' : 'dark';
    const iconPath = '/Tech%20Pulse%20-%20Comlab%20Monitoring%20System/assets/img/';

    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    document.querySelectorAll('.theme-toggle').forEach(function (button) {
        const icon = button.querySelector('img');

        button.setAttribute('aria-label', 'Switch to ' + nextTheme + ' mode');

        if (icon) {
            icon.src = iconPath + nextTheme + '-mode.png';
            icon.alt = nextTheme === 'light' ? 'Light mode' : 'Dark mode';
        }
    });
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');

    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
}

applyTheme(document.documentElement.getAttribute('data-theme') || 'light');

document.querySelectorAll('.theme-toggle').forEach(function (button) {
    button.addEventListener('click', toggleTheme);
});
