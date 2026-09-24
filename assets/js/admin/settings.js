// assets/js/admin/settings.js
document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggle
    const toggleBtns = document.querySelectorAll('.password-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.getAttribute('data-target'));
            if(target) {
                target.type = target.type === 'password' ? 'text' : 'password';
                btn.textContent = target.type === 'password' ? 'visibility' : 'visibility_off';
            }
        });
    });

    // Facility Auto-Fill Logic
    const facSelect = document.getElementById('facilitySelect');
    if (facSelect && typeof facilityData !== 'undefined') {
        facSelect.addEventListener('change', function() {
            const fac = facilityData.find(f => f.Facility_ID == this.value);
            if (fac) {
                document.getElementById('fac_name').value = fac.Facility_Name;
                document.getElementById('fac_location').value = fac.Location;
                document.getElementById('fac_capacity').value = fac.Capacity;
                document.getElementById('fac_open').value = fac.Open_Time;
                document.getElementById('fac_close').value = fac.Close_Time;
            } else {
                document.getElementById('fac_name').value = '';
                document.getElementById('fac_location').value = '';
                document.getElementById('fac_capacity').value = '';
                document.getElementById('fac_open').value = '';
                document.getElementById('fac_close').value = '';
            }
        });
    }
});