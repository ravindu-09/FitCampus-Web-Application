document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');

    // 1. Password visibility toggle
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePasswordBtn.textContent = isPassword ? 'visibility_off' : 'visibility';
        });
    }

    // 2. Authenticating loading spinner micro-interaction
    if (form && submitBtn && passwordInput) {
        form.addEventListener('submit', (e) => {
            const identifier = document.getElementById('identifier').value.trim();
            const password = passwordInput.value;

            if (!identifier || !password) {
                e.preventDefault();
                return;
            }

            submitBtn.classList.remove('animate-pulse-cta');
            submitBtn.style.pointerEvents = 'none';
            submitBtn.innerHTML = `
                <svg style="animation: spin 1s linear infinite; height: 20px; width: 20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Authenticating...</span>
            `;
        });
    }
});

// Loading spinner rotation keyframe
const spinStyle = document.createElement('style');
spinStyle.innerHTML = `@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`;
document.head.appendChild(spinStyle);