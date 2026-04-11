document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (!passwordInput || !togglePassword) {
        return;
    }

    const updatePasswordState = (visible) => {
        passwordInput.type = visible ? 'text' : 'password';
        togglePassword.classList.toggle('is-visible', visible);
        togglePassword.setAttribute('aria-pressed', visible ? 'true' : 'false');
        togglePassword.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        togglePassword.setAttribute('title', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
    };

    togglePassword.addEventListener('click', () => {
        const isVisible = passwordInput.type === 'text';
        updatePasswordState(!isVisible);
    });

    updatePasswordState(false);
});