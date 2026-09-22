// assets/js/member/teams.js
document.addEventListener('DOMContentLoaded', () => {

    const teamData = {
        "UOC Cricket": {
            workouts: [
                {
                    title: "Pre-Season Conditioning",
                    meta: "Uploaded by Captain • Today",
                    tagClass: "tag-green",
                    tagText: "Mandatory",
                    borderClass: "border-left-secondary",
                    exercises: [
                        { name: "Interval Sprints", reps: "10 x 100m" },
                        { name: "Plyometric Jumps", reps: "4 x 15" }
                    ],
                    btnClass: "btn-secondary-action"
                },
                {
                    title: "Core & Stability",
                    meta: "Uploaded by Coach • Yesterday",
                    tagClass: "tag-purple",
                    tagText: "Optional",
                    borderClass: "border-left-primary",
                    exercises: [
                        { name: "Plank Holds", reps: "3 x 60s" },
                        { name: "Russian Twists", reps: "3 x 20" }
                    ],
                    btnClass: "btn-primary"
                }
            ],
            goals: [
                {
                    title: "Inter-University Championship",
                    category: "Tournament Prep",
                    target: "100% Attendance",
                    current: "85%",
                    progress: 85,
                    time: "10 Days Left",
                    badgeBg: "badge-bg-green",
                    badgeTxt: "txt-green",
                    progressBg: "bg-green"
                },
                {
                    title: "Squad Yo-Yo Test Average",
                    category: "Endurance Target",
                    target: "Level 18.5",
                    current: "Level 17.2",
                    progress: 70,
                    time: "End of Month",
                    badgeBg: "badge-bg-purple",
                    badgeTxt: "txt-purple",
                    progressBg: "bg-purple"
                }
            ]
        },
        "UOC Athletics": {
            workouts: [
                {
                    title: "Track Speed Drills",
                    meta: "Uploaded by Coach • Today",
                    tagClass: "tag-green",
                    tagText: "Mandatory",
                    borderClass: "border-left-primary",
                    exercises: [
                        { name: "400m Sprints", reps: "5 x 1" },
                        { name: "High Knees", reps: "3 x 30s" }
                    ],
                    btnClass: "btn-primary"
                }
            ],
            goals: [] 
        }
    };

    const teamModal = document.getElementById('team-selection-modal');
    const teamToggleBtn = document.getElementById('team-toggle-btn');
    const activeTeamName = document.getElementById('active-team-name');
    const workoutsContainer = document.getElementById('team-workouts-container');
    const goalsContainer = document.getElementById('team-goals-container');

    if (teamToggleBtn) {
        teamToggleBtn.addEventListener('click', () => {
            if(teamModal) teamModal.classList.add('active');
        });
    }

    window.closeModals = function() {
        if(teamModal) teamModal.classList.remove('active');
        const detailsModal = document.getElementById('team-goal-details-modal');
        if(detailsModal) detailsModal.classList.remove('active');
    };

    const btnCloseTeam = document.getElementById('btn-close-team-modal');
    if (btnCloseTeam) btnCloseTeam.addEventListener('click', window.closeModals);

    function renderWorkouts(teamName) {
        if(!workoutsContainer) return;
        const workouts = teamData[teamName]?.workouts || [];
        
        if(workouts.length === 0) {
            workoutsContainer.innerHTML = `
                <div class="glass-card tm-empty-state">
                    <span class="material-symbols-outlined tm-empty-icon">fitness_center</span>
                    <p class="m-0 text-color-on-surface-variant">No team workouts uploaded yet.</p>
                </div>
            `;
            return;
        }

        let html = '';
        workouts.forEach(w => {
            let exercisesHtml = w.exercises.map(e => `
                <li>
                    <div class="ex-name-plain">${e.name}</div>
                    <div class="ex-reps-plain">${e.reps}</div>
                </li>
            `).join('');

            html += `
                <div class="glass-card wk-card ${w.borderClass}">
                    <div class="wk-card-body">
                        <div class="wk-team-card-head mb-4">
                            <div>
                                <h4 class="wk-card-title m-0">${w.title}</h4>
                                <p class="wk-card-meta m-0">${w.meta}</p>
                            </div>
                            <span class="wk-tag ${w.tagClass} m-0">${w.tagText}</span>
                        </div>
                        <ul class="wk-exercise-list mb-6">
                            ${exercisesHtml}
                        </ul>
                        <button class="btn ${w.btnClass} tm-btn-full mt-auto">Start Team Session</button>
                    </div>
                </div>
            `;
        });
        workoutsContainer.innerHTML = html;
    }

    function renderGoals(teamName) {
        if(!goalsContainer) return;
        const goals = teamData[teamName]?.goals || [];

        if(goals.length === 0) {
            goalsContainer.innerHTML = `
                <div class="glass-card tm-empty-state tm-empty-full">
                    <span class="material-symbols-outlined tm-empty-icon">flag</span>
                    <p class="m-0 text-color-on-surface-variant">No active team goals at the moment.</p>
                </div>
            `;
            return;
        }

        let html = '';
        goals.forEach(g => {
            html += `
                <div class="gl-goal-card">
                    <div class="gl-card-top">
                        <span class="gl-badge ${g.badgeBg} ${g.badgeTxt}">${g.category}</span>
                        <span class="gl-label-muted mono text-xs">${teamName}</span>
                    </div>
                    <h4 class="gl-card-title">${g.title}</h4>
                    
                    <div class="gl-metrics">
                        <div>
                            <div class="gl-m-lbl">Target Metric</div>
                            <div class="gl-m-val">${g.target}</div>
                        </div>
                        <div class="text-right">
                            <div class="gl-m-lbl">Current</div>
                            <div class="gl-m-val">${g.current}</div>
                        </div>
                    </div>
                    
                    <div class="gl-progress-bg">
                        <div class="gl-progress-fill ${g.progressBg}" style="width: ${g.progress}%;"></div>
                    </div>
                    
                    <div class="gl-progress-lbls">
                        <span class="txt-white">${g.progress}% Progress</span>
                        <span>${g.time}</span>
                    </div>

                    <div class="gl-card-footer">
                        <div class="gl-plan">
                            <span class="material-symbols-outlined">flag</span>
                            <div class="gl-plan-text txt-white">Team Target</div>
                        </div>
                        <button class="gl-view-btn txt-purple" onclick="openTeamGoalDetailsModal('${g.title}', ${g.progress}, '${g.target}', '${g.current}', '${g.badgeBg} ${g.badgeTxt}', '${g.category.toUpperCase()}', '${g.time}')">View<br>Details</button>
                    </div>
                </div>
            `;
        });
        goalsContainer.innerHTML = html;
    }

    window.switchToTeam = function(teamName) {
        if(activeTeamName) {
            activeTeamName.innerText = teamName;
        }
        renderWorkouts(teamName);
        renderGoals(teamName);
        window.closeModals();
    };

    window.openTeamGoalDetailsModal = function(title, percentage, target, current, badgeClass, badgeText, timeText) {
        const detailsModal = document.getElementById('team-goal-details-modal');
        
        if(detailsModal) {
            document.getElementById('team-detail-title').innerText = title;
            document.getElementById('team-detail-target-val').innerText = target;
            document.getElementById('team-detail-current-val').innerText = current;
            document.getElementById('team-detail-percentage').innerText = percentage + '%';
            
            const timeBadge = document.getElementById('team-detail-time');
            if(timeBadge) timeBadge.innerText = timeText;
            
            const badge = document.getElementById('team-detail-category-badge');
            if (badge) {
                badge.className = `gl-badge ${badgeClass} uppercase gl-badge-sm`;
                badge.innerText = badgeText;
            }
            
            const barFill = document.getElementById('team-bar-progress-fill');
            if (barFill) {
                const cleanPercentage = Math.min(Math.max(parseFloat(percentage), 0), 100);
                barFill.style.transition = 'none';
                barFill.style.width = '0%';
                barFill.getBoundingClientRect(); 
                
                setTimeout(() => {
                    barFill.style.transition = 'width 1.2s cubic-bezier(0.4, 0, 0.2, 1)';
                    barFill.style.width = cleanPercentage + '%';
                }, 50);
            }

            detailsModal.classList.add('active');
        }
    };

    window.closeTeamGoalModal = function() {
        const detailsModal = document.getElementById('team-goal-details-modal');
        if(detailsModal) detailsModal.classList.remove('active');
    };

    // Initialize
    switchToTeam('UOC Cricket');

});