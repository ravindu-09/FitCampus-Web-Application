// assets/js/member/leaderboard.js

document.addEventListener('DOMContentLoaded', () => {

    // Tab switching interaction for Leaderboard
    const tabs = document.querySelectorAll('.ld-tab');
    
    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Future Implementation: 
                // Here you can add AJAX/Fetch logic to load table data based on the selected tab
                // e.g., loadLeaderboardData(tab.innerText);
            });
        });
    }

});