const loginForm = document.getElementById('loginForm');

if (loginForm) {
    loginForm.addEventListener('submit', function () {
        const submitButton = loginForm.querySelector('.submit-btn');

        submitButton.disabled = true;
        submitButton.textContent = 'Logging in...';
    });
}
