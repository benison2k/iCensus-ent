document.addEventListener('DOMContentLoaded', () => {
    // --- "ESCAPE" KEY TO CLOSE MODALS ---
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            // Find all modals, filter for the one that is currently visible
            const visibleModal = Array.from(document.querySelectorAll('.modal')).find(
                (modal) => modal.style.display !== 'none' && modal.style.display !== ''
            );

            if (visibleModal) {
                // If a visible modal is found, hide it
                visibleModal.style.display = 'none';
            }
        }
    });

    // --- "BACK TO TOP" BUTTON LOGIC ---
    const backToTopButton = document.getElementById('backToTopBtn');
    const pageSizeSelect = document.getElementById('pageSizeSelect');

    // Only proceed if the button and the page size selector exist on the page
    if (backToTopButton && pageSizeSelect) {
        const checkVisibility = () => {
            const shouldBeVisible = parseInt(pageSizeSelect.value, 10) >= 25 && window.scrollY > 300;
            backToTopButton.style.display = shouldBeVisible ? 'flex' : 'none';
        };

        // Check visibility on scroll and when the page size changes
        window.addEventListener('scroll', checkVisibility);
        pageSizeSelect.addEventListener('change', checkVisibility);

        // Initial check in case the page loads scrolled down or with a large page size
        checkVisibility();

        // Scroll to top when the button is clicked
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});