document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('supportRequestForm');
    if (!form) return;

    const extensionFields = document.getElementById('extensionFields');
    const requestedEndDate = document.getElementById('requestedEndDate');
    const attachment = document.getElementById('supportAttachment');
    const attachmentRequirement = document.getElementById('attachmentRequirement');
    const attachmentHelp = document.getElementById('attachmentHelp');
    const descriptionLabel = document.getElementById('descriptionLabel');

    form.querySelectorAll('input[name="category"]').forEach((choice) => {
        choice.addEventListener('change', () => {
            const isExtension = choice.value === 'account_extension' && choice.checked;
            extensionFields.hidden = !isExtension;
            requestedEndDate.required = isExtension;
            attachment.required = isExtension;
            attachmentRequirement.textContent = isExtension ? 'Required' : 'Optional';
            attachmentHelp.textContent = isExtension
                ? 'A clear photo of your new student ID is required. JPEG, PNG or WEBP, up to 5 MB.'
                : 'JPEG, PNG or WEBP, up to 5 MB. Attachments are visible only to you and administrators.';
            descriptionLabel.innerHTML = isExtension
                ? 'Reason for your extension request <span>*</span>'
                : 'Describe your request <span>*</span>';
        });
    });
});