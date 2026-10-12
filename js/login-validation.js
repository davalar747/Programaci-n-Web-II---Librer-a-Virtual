document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.getElementById('loginEmail');
    const passwordInput = document.getElementById('loginPassword');
    const btnLogin = document.getElementById('btnLogin');

    // Estado individual de cada campo
    let isEmailValid = false;
    let isPasswordValid = false;

    // Expresión regular para correo electrónico estándar
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Expresión regular para contraseña segura:
    // Al menos 8 caracteres, 1 mayúscula, 1 número y 1 carácter especial
    const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;

    // Función auxiliar para actualizar clases visuales de Bootstrap
    function setFieldStatus(inputElement, isValid) {
        if (isValid) {
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        } else {
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    // Actualizar el estado del botón submit
    function toggleSubmitButton() {
        btnLogin.disabled = !(isEmailValid && isPasswordValid);
    }

    // Validación en tiempo real del correo
    emailInput.addEventListener('input', () => {
        const value = emailInput.value.trim();
        isEmailValid = emailRegex.test(value);
        setFieldStatus(emailInput, isEmailValid);
        toggleSubmitButton();
    });

    // Validación en tiempo real de la contraseña segura
    passwordInput.addEventListener('input', () => {
        const value = passwordInput.value;
        isPasswordValid = passwordRegex.test(value);
        setFieldStatus(passwordInput, isPasswordValid);
        toggleSubmitButton();
    });
});