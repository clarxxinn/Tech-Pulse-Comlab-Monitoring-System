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

const registerForm = document.getElementById('registerForm');
if (registerForm) {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const confirmError = document.getElementById('confirmError');

    function checkMatch() {
        if (!confirmPassword.value) {
            confirmError.style.display = 'none';
            return true;
        }
        const matches = password.value === confirmPassword.value;
        confirmError.style.display = matches ? 'none' : 'block';
        return matches;
    }

    password.addEventListener('input', checkMatch);
    confirmPassword.addEventListener('input', checkMatch);

    registerForm.addEventListener('submit', function (e) {
        if (!checkMatch()) {
            e.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            confirmPassword.focus();
            return;
        }
        const submitBtn = registerForm.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';
    });
}

const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function () {
        const submitBtn = loginForm.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
    });
}