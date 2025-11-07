// public/assets/js/system_logs.js

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 'Go to Page' Button Logic ---
    const gotoPageBtn = document.getElementById('gotoPageBtn');
    if (gotoPageBtn) {
        gotoPageBtn.addEventListener('click', function() {
            const pageNum = document.getElementById('gotoPageInput').value;
            if (pageNum) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', pageNum);
                window.location.href = currentUrl.href;
            }
        });
    }

    // --- Auto-Submit Filter Logic ---
    document.querySelectorAll('.auto-submit-filter').forEach(function(element) {
        element.addEventListener('change', function() {
            if (this.id === 'pageSizeSelect') {
                // Submit the page size form
                const pageSizeForm = document.getElementById('pageSizeForm');
                if (pageSizeForm) pageSizeForm.submit();
            } else {
                // Submit the main filter form
                const filterForm = document.getElementById('filterForm');
                if (filterForm) filterForm.submit();
            }
        });
    });

    // --- Mark Log as Seen Logic ---
    const logTableBody = document.getElementById('logTableBody');
    if (logTableBody) {
        logTableBody.addEventListener('click', function(e) {
            const row = e.target.closest('tr.new-log');
            if (row) {
                const logId = row.dataset.id;
                // Instantly remove the 'new' highlighting
                row.classList.remove('new-log');
                
                const formData = new FormData();
                formData.append('id', logId);

                // Send a request to the server to mark it as seen
                fetch('/iCensus-ent/public/sysadmin/logs/mark-as-seen', {
                    method: 'POST',
                    body: formData
                }).catch(error => console.error('Error marking log as seen:', error));
            }
        });
    }

    // --- Mark All as Seen Logic ---
    const markAllSeenBtn = document.getElementById('markAllSeenBtn');
    if (markAllSeenBtn) {
        markAllSeenBtn.addEventListener('click', function() {
            fetch('/iCensus-ent/public/sysadmin/logs/mark-all-as-seen', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove highlighting from all rows
                    document.querySelectorAll('tr.new-log').forEach(row => {
                        row.classList.remove('new-log');
                    });
                }
            })
            .catch(error => console.error('Error marking all logs as seen:', error));
        });
    }

    // --- Scroll Position Persistence ---
    function saveScrollPosition() {
        sessionStorage.setItem('logScrollPosition', window.scrollY);
    }

    // Save scroll on pagination, filter, or page size change
    document.querySelectorAll('.pagination-control').forEach(el => {
        el.addEventListener('click', saveScrollPosition);
    });
    document.querySelectorAll('#filterForm, #pageSizeForm').forEach(form => {
        form.addEventListener('submit', saveScrollPosition);
    });

    // Restore scroll on page load
    window.addEventListener('load', () => {
        const scrollPosition = sessionStorage.getItem('logScrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition, 10));
            sessionStorage.removeItem('logScrollPosition'); // Clear after restoring
        }
    });

});