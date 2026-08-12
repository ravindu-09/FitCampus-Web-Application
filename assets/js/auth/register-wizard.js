document.addEventListener('DOMContentLoaded', () => {
    const step3Form = document.getElementById('step3Form');
    if (step3Form) {
        step3Form.addEventListener('submit', (e) => {
            const btn = document.getElementById('completeBtn');
            if (btn) {
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Verifying...';
                btn.style.opacity = '0.8';
            }
        });
    }
});