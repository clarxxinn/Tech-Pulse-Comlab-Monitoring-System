document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? 'Hide' : 'Show';
        btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });
});

const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function () {
        const submitBtn = loginForm.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
    });
}