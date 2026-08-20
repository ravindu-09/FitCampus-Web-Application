
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('verificationModal');
    const closeBtn = document.getElementById('modalCloseBtn');
    const rejectToggleBtn = document.getElementById('btnRejectToggle');
    const approveBtn = document.getElementById('btnApproveAction');
    const searchInput = document.getElementById('userSearchInput');

    // 1. Inspect Document Modal Triggers
    document.querySelectorAll('.btn-inspect-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            const userData = JSON.parse(btn.getAttribute('data-user'));
            openVerificationModal(userData);
        });
    });

    // 2. Close Modal Listeners
    if (closeBtn) closeBtn.addEventListener('click', closeVerificationModal);

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeVerificationModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeVerificationModal();
    });

    // 3. Reject Toggle Behavior
    if (rejectToggleBtn) {
        rejectToggleBtn.addEventListener('click', handleRejectClick);
    }

    // 4. Modal Approve Action
    if (approveBtn) {
        approveBtn.addEventListener('click', () => submitDecision('approve'));
    }

    // 5. Live Search Filter
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.verification-table-row');

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                row.style.display = searchData.includes(query) ? '' : 'none';
            });
        });
    }
});

/**
 * Open Modal with Population
 */
function openVerificationModal(user) {
    const modal = document.getElementById('verificationModal');
    if (!modal) return;

    document.getElementById('formUserId').value = user.user_id;
    document.getElementById('modalUserName').textContent = user.full_name;
    document.getElementById('modalUserSubtitle').textContent = (user.faculty || 'Undergraduate') + ' • Member ID: #' + user.user_id;
    document.getElementById('modalRegNo').textContent = user.reg_no || 'UOC-Pending';
    document.getElementById('modalEmail').textContent = user.email || 'N/A';
    
    // Database schema: emergency_contact mapping
    document.getElementById('modalPhone').textContent = user.emergency_contact || user.contact_no || '+94 7X XXX XXXX';
    document.getElementById('modalDate').textContent = user.created_at || 'Just Now';

    // Set Avatar & Documents
    document.getElementById('modalAvatar').src = '../../assets/images/uploads/' + (user.profile_image || 'default_avatar.png');
    document.getElementById('modalIdFront').src = '../../assets/images/uploads/' + (user.id_front_image || 'default_id_front.png');
    document.getElementById('modalIdBack').src = '../../assets/images/uploads/' + (user.id_back_image || 'default_id_back.png');

    // Reset Rejection State
    const rejectionGroup = document.getElementById('rejectionGroup');
    if (rejectionGroup) {
        rejectionGroup.classList.add('hidden');
        document.getElementById('rejectionReasonInput').value = '';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

/**
 * Close Modal
 */
function closeVerificationModal() {
    const modal = document.getElementById('verificationModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

/**
 * Toggle Reject Feedback Area
 */
function handleRejectClick() {
    const group = document.getElementById('rejectionGroup');
    const formAction = document.getElementById('formAction');
    const reasonInput = document.getElementById('rejectionReasonInput');

    if (group.classList.contains('hidden')) {
        group.classList.remove('hidden');
        reasonInput.focus();
        formAction.value = 'reject';
    } else {
        if (!reasonInput.value.trim()) {
            alert('Please provide a reason for declining verification.');
            reasonInput.focus();
            return;
        }
        submitDecision('reject');
    }
}

/**
 * Submit Modal Form Decision
 */
function submitDecision(actionType) {
    const form = document.getElementById('verifyForm');
    const formAction = document.getElementById('formAction');
    if (form && formAction) {
        formAction.value = actionType;
        form.submit();
    }
}

/**
 * Quick Approve/Decline from Table Row
 */
function quickDecision(userId, action) {
    const form = document.getElementById('quickDecisionForm');
    const userIdInput = document.getElementById('quickUserId');
    const actionInput = document.getElementById('quickAction');
    const reasonInput = document.getElementById('quickReason');

    if (action === 'reject') {
        const defaultPromptReason = 'Provided verification documents or academic details did not meet the required criteria.';
        const reason = prompt('Please enter rejection reason:', defaultPromptReason);
        
        if (reason === null) return; // User cancelled prompt

        userIdInput.value = userId;
        actionInput.value = 'reject';
        
        // Pass custom or fallback reason to quick form input
        if (reasonInput) {
            reasonInput.value = reason.trim() !== '' ? reason.trim() : defaultPromptReason;
        }
        
        form.submit();
    } else {
        if (confirm('Approve this student member immediately?')) {
            userIdInput.value = userId;
            actionInput.value = 'approve';
            form.submit();
        }
    }
}

/**
 * Zoom Inspection Trigger
 */
function zoomImage(wrapper) {
    const img = wrapper.querySelector('img');
    if (img && img.src) {
        window.open(img.src, '_blank');
    }
}