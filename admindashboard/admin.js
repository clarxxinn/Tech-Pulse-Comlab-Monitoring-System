// Admin dashboard — small, page-agnostic behaviours (vanilla JS).
document.addEventListener('DOMContentLoaded', function () {
    // Password show/hide: any .toggle-password button with a data-target id.
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            if (!input) return;
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            btn.textContent = reveal ? 'Hide' : 'Show';
            btn.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
        });
    });

    // Confirm destructive actions: any form carrying data-confirm="message".
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
