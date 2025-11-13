// public/assets/js/reset_password.js

document.addEventListener('DOMContentLoaded', () => {
    const passwordField = document.getElementById('passwordField');
    const confirmPasswordField = document.getElementById('confirmPasswordField');
    const togglePassword1 = document.getElementById('togglePassword1');
    const togglePassword2 = document.getElementById('togglePassword2');

    if (togglePassword1 && passwordField) {
        togglePassword1.addEventListener('click', () => {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            togglePassword1.textContent = type === 'password' ? 'visibility_off' : 'visibility';
        });
    }

    if (togglePassword2 && confirmPasswordField) {
        togglePassword2.addEventListener('click', () => {
            const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.setAttribute('type', type);
            togglePassword2.textContent = type === 'password' ? 'visibility_off' : 'visibility';
        });
    }
});