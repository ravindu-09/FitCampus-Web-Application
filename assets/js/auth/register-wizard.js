
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Password Visibility Toggles
    const togglePass = document.getElementById('togglePassword');
    const passInput  = document.getElementById('password');
    const toggleConf = document.getElementById('toggleConfirmPassword');
    const confInput  = document.getElementById('confirm_password');

    if (togglePass && passInput) {
        togglePass.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (passInput.type === 'password') {
                passInput.type = 'text';
                togglePass.textContent = 'visibility_off';
            } else {
                passInput.type = 'password';
                togglePass.textContent = 'visibility';
            }
        });
    }

    if (toggleConf && confInput) {
        toggleConf.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (confInput.type === 'password') {
                confInput.type = 'text';
                toggleConf.textContent = 'visibility_off';
            } else {
                confInput.type = 'password';
                toggleConf.textContent = 'visibility';
            }
        });
    }

    // 2. File Upload Live Preview
    const setupUploadPreview = (inputId, boxId, textId, defaultText) => {
        const input = document.getElementById(inputId);
        const box = document.getElementById(boxId);
        const text = document.getElementById(textId);

        if (input && box && text) {
            input.addEventListener('change', () => {
                if (input.files && input.files[0]) {
                    const fileName = input.files[0].name;
                    text.textContent = fileName.length > 20 ? fileName.substring(0, 18) + '...' : fileName;
                    box.style.borderColor = 'var(--secondary)';
                    box.style.backgroundColor = 'rgba(74, 225, 118, 0.08)';
                } else {
                    text.textContent = defaultText;
                    box.style.borderColor = 'rgba(76, 68, 82, 0.5)';
                    box.style.backgroundColor = 'transparent';
                }
            });
        }
    };

    setupUploadPreview('id_front', 'box_front', 'txt_front', 'Front View');
    setupUploadPreview('id_back', 'box_back', 'txt_back', 'Back View');
    setupUploadPreview('profile_image', 'box_profile', 'txt_profile', 'Profile Photo');

    // 3. Form Submit Spinner
    const step3Form = document.getElementById('step3Form');
    const submitRegBtn = document.getElementById('submitRegBtn');

    if (step3Form && submitRegBtn) {
        step3Form.addEventListener('submit', () => {
            submitRegBtn.classList.remove('animate-pulse-cta');
            submitRegBtn.style.pointerEvents = 'none';
            submitRegBtn.innerHTML = `
                <svg style="animation: spin 1s linear infinite; height: 20px; width: 20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Submitting Application...</span>
            `;
        });
    }
});