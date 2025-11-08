// public/assets/js/resident_modal.js

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('residentModal');
    if (!modal) return;
    
    const closeBtn = modal.querySelector('.close');
    const tabButtons = modal.querySelectorAll('.tab-button');
    const tabContents = modal.querySelectorAll('.tab-content');
    const progressBar = document.getElementById('formProgressBar');
    const form = document.getElementById('residentForm');
    const requiredFields = Array.from(form.querySelectorAll('[required]'));
    const totalRequired = requiredFields.length;

    // ✅ NEW: Get the progress label element
    const progressLabel = document.getElementById('formProgressLabel');

    function updateProgress() {
        if (!progressBar) return;
        let completedCount = 0;
        requiredFields.forEach(field => {
            if (field.value.trim() !== '') {
                completedCount++;
            }
        });
        const percentage = totalRequired > 0 ? (completedCount / totalRequired) * 100 : 0;
        
        // ✅ MODIFICATION: Update the bar's width
        progressBar.style.width = percentage + '%';

        // ✅ MODIFICATION: Update the label's text
        if (progressLabel) {
            progressLabel.textContent = `Completeness: ${Math.round(percentage)}% (${completedCount} of ${totalRequired} required fields)`;
        }
        
        // ✅ REMOVED: The old line that put text inside the bar
        // progressBar.textContent = `${Math.round(percentage)}% ...`;
    }

    requiredFields.forEach(field => {
        field.addEventListener('input', updateProgress);
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            button.classList.add('active');
            modal.querySelector(`#tab-${button.dataset.tab}`).classList.add('active');
        });
    });

    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });

    // This logic handles dark mode persistence inside the modal
    const body = document.body;
    const observer = new MutationObserver(() => {
        const isDarkMode = body.classList.contains('dark-mode');
        const modalContent = modal.querySelector('.modal-modern-content');
        if(isDarkMode) {
            modalContent.classList.add('dark');
        } else {
            modalContent.classList.remove('dark');
        }
    });
    observer.observe(body, { attributes: true, attributeFilter: ['class'] });

    // Expose the updateProgress function to be callable from other scripts
    modal.updateProgress = updateProgress;
});