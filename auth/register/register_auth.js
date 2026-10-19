const registerForm = document.getElementById('registerForm');

if (registerForm) {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const confirmError = document.getElementById('confirmError');

    function passwordsMatch() {
        if (!confirmPassword.value) {
            confirmError.style.display = 'none';
            return true;
        }

        const matches = password.value === confirmPassword.value;
        confirmError.style.display = matches ? 'none' : 'block';
        return matches;
    }

    password.addEventListener('input', passwordsMatch);
    confirmPassword.addEventListener('input', passwordsMatch);

    registerForm.addEventListener('submit', function (event) {
        if (!passwordsMatch()) {
            event.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            confirmPassword.focus();
            return;
        }

        const submitButton = registerForm.querySelector('.submit-btn');
        submitButton.disabled = true;
        submitButton.textContent = 'Creating account...';
    });
}
