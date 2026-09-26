// assets/js/admin/users.js
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('filterSearch');
    const roleSelect = document.getElementById('filterRole');
    const teamSelect = document.getElementById('filterTeam');
    const rows = document.querySelectorAll('.user-data-row');

    function filterTable() {
        const query = (searchInput?.value || '').toLowerCase().trim();
        const role = roleSelect?.value || 'all';
        const team = teamSelect?.value || 'all';

        rows.forEach(row => {
            const rSearch = row.getAttribute('data-search') || '';
            const rRole = row.getAttribute('data-role') || '';
            const rTeamsRaw = row.getAttribute('data-teams') || '';
            const rTeams = rTeamsRaw ? rTeamsRaw.split(',') : [];

            const matchSearch = rSearch.includes(query);
            const matchRole = (role === 'all' || rRole === role);
            const matchTeam = (team === 'all' || rTeams.includes(String(team)));

            if (matchSearch && matchRole && matchTeam) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if(searchInput) searchInput.addEventListener('input', filterTable);
    if(roleSelect) roleSelect.addEventListener('change', filterTable);
    if(teamSelect) teamSelect.addEventListener('change', filterTable);

    // View User Functionality
    document.querySelectorAll('.btn-view-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const userData = JSON.parse(this.getAttribute('data-user'));
            openViewModal(userData);
        });
    });

    // Edit Role Functionality 
    document.querySelectorAll('.btn-role-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const userData = JSON.parse(this.getAttribute('data-user'));
            const teamsList = (typeof globalTeamsList !== 'undefined') ? globalTeamsList : (window.globalTeamsList || []);
            openRoleModal(userData, teamsList);
        });
    });
});

// View Modal Logic
function openViewModal(user) {
    document.getElementById('v-userid').value = user.User_ID;
    document.getElementById('v-name').textContent = user.First_Name + ' ' + user.Last_Name;
    document.getElementById('v-email').textContent = user.Email;
    document.getElementById('v-avatar').src = '../../assets/images/uploads/' + (user.Profile_Image || 'default_avatar.png');
    document.getElementById('v-reg').textContent = user.Registration_Number || 'N/A';
    document.getElementById('v-fac').textContent = user.Faculty || 'N/A';
    document.getElementById('v-nic').textContent = user.NIC || 'N/A';
    document.getElementById('v-phone').textContent = user.Emergency_Contact || 'N/A';

    const teamsDiv = document.getElementById('v-teams');
    if (user.parsed_teams && user.parsed_teams.length > 0) {
        teamsDiv.innerHTML = user.parsed_teams.map(t => {
            const roleColor = t.role === 'Captain' ? 'color: var(--tertiary);' : 'color: #aaa;';
            return `<div style="margin-bottom: 4px;">• ${t.name} <span style="${roleColor} font-size: 11px;">(${t.role})</span></div>`;
        }).join('');
    } else {
        teamsDiv.innerHTML = '<span class="txt-muted">Not assigned to any teams.</span>';
    }

    const updateSection = document.getElementById('student-update-section');
    if (user.Role && user.Role.toLowerCase() === 'student') {
        updateSection.style.display = 'block';
        document.getElementById('v-life').value = user.Life_Percentage || 100;
        document.getElementById('v-exam').value = user.Date_of_Final_Exam || '';
    } else {
        updateSection.style.display = 'none';
    }

    openModal('viewUserModal');
}

window.updateStudentMeta = function() {
    const userId = document.getElementById('v-userid').value;
    const life = document.getElementById('v-life').value;
    const exam = document.getElementById('v-exam').value;

    const formData = new FormData();
    formData.append('action', 'update_meta');
    formData.append('user_id', userId);
    formData.append('life_percentage', life);
    formData.append('final_exam', exam);
    
    if (typeof csrfToken !== 'undefined') formData.append('csrf_token', csrfToken);

    // Path updated to controllers
    fetch('../../controllers/admin/user_manage_action.php', { method: 'POST', body: formData })
        .then(res => {
            if (!res.ok) throw new Error("Server or Network error");
            return res.json();
        })
        .then(data => {
            if (data.success) {
                alert("Student details updated successfully!");
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(err => {
            alert("Failed to update: " + err.message);
        });
};

function openRoleModal(user, allTeams) {
    document.getElementById('r-userid').value = user.User_ID;
    document.getElementById('r-name').textContent = user.First_Name + ' ' + user.Last_Name;
    
    const demoteSection = document.getElementById('demote-section');
    const demoteList = document.getElementById('current-captain-teams');
    const promoteSelect = document.getElementById('r-team');
    
    demoteList.innerHTML = '';
    promoteSelect.innerHTML = '<option value="">-- Select Team --</option>';
    
    let captainTeamIds = [];

    // 1. Find the teams where the user is currently a Captain and populate the demote section
    if (user.parsed_teams && Array.isArray(user.parsed_teams)) {
        user.parsed_teams.forEach(t => {
            if (t.role === 'Captain') {
                captainTeamIds.push(String(t.id));
                demoteList.innerHTML += `
                    <div class="flex-between bg-dim p-3 rounded-lg border-dim mb-2">
                        <span class="txt-white font-bold">${t.name}</span>
                        <button type="button" class="btn btn-sm text-error" style="border: 1px solid rgba(255,0,0,0.3); padding: 4px 8px;" onclick="submitRoleChange('demote', ${t.id})">Demote</button>
                    </div>
                `;
            }
        });
    }

    if (captainTeamIds.length > 0) {
        demoteSection.classList.remove('hidden');
        demoteSection.style.display = 'block';
    } else {
        demoteSection.classList.add('hidden');
        demoteSection.style.display = 'none';
    }

    // 2. Populate the promote dropdown with teams where the user is not currently a Captain
    if (allTeams && allTeams.length > 0) {
        allTeams.forEach(team => {
            const currentTeamId = String(team.Team_ID);
            if (!captainTeamIds.includes(currentTeamId)) {
                promoteSelect.innerHTML += `<option value="${currentTeamId}">${team.Team_Name}</option>`;
            }
        });
    }

    openModal('editRoleModal');
}

window.submitRoleChange = function(actionType, explicitTeamId = null) {
    const userId = document.getElementById('r-userid').value;
    const teamId = explicitTeamId || document.getElementById('r-team').value;

    if (!teamId) {
        alert("Please select a team.");
        return;
    }

    const actionText = actionType === 'promote' ? 'promote this user to Captain' : 'demote this Captain to Member';
    if (!confirm(`Are you sure you want to ${actionText}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', actionType === 'promote' ? 'promote_captain' : 'demote_captain');
    formData.append('user_id', userId);
    formData.append('team_id', teamId);
    
    if (typeof csrfToken !== 'undefined') formData.append('csrf_token', csrfToken);

    // Path updated to controllers
    fetch('../../controllers/admin/user_manage_action.php', { method: 'POST', body: formData })
        .then(res => {
            if (!res.ok) throw new Error("Server or Network error");
            return res.json();
        })
        .then(data => {
            if (data.success) {
                alert(`Role updated successfully!`);
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(err => {
            alert("Failed to update: " + err.message);
        });
};

window.openModal = function(id) {
    const m = document.getElementById(id);
    if(m) {
        m.classList.remove('hidden');
        m.style.display = 'flex';
    }
};

window.closeModal = function(id) {
    const m = document.getElementById(id);
    if(m) {
        m.classList.add('hidden');
        m.style.display = 'none';
    }
};