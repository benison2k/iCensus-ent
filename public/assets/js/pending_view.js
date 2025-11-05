// public/assets/js/pending_view.js

document.addEventListener('DOMContentLoaded', function () {
    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const gotoPageBtn = document.getElementById('gotoPageBtn');
    const gotoPageInput = document.getElementById('gotoPage');
    
    /**
     * Updates the URL with a new query parameter and navigates to it.
     * @param {string} key The query parameter key (e.g., 'page' or 'pageSize')
     * @param {string} value The new value
     */
    const updateUrlAndNavigate = (key, value) => {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        // Reset to page 1 if the page size is changed
        if (key !== 'page') url.searchParams.set('page', 1);
        window.location.href = url.toString();
    };

    // --- EVENT LISTENERS for server-side pagination ---

    pageSizeSelect.addEventListener('change', () => updateUrlAndNavigate('pageSize', pageSizeSelect.value));
    
    prevPageBtn.addEventListener('click', () => {
        const url = new URL(window.location.href);
        const currentPage = parseInt(url.searchParams.get('page') || '1', 10);
        if (currentPage > 1) {
             // Manually set 'page' param and navigate
             url.searchParams.set('page', currentPage - 1);
             window.location.href = url.toString();
        }
    });
    
    nextPageBtn.addEventListener('click', () => {
         const url = new URL(window.location.href);
         const currentPage = parseInt(url.searchParams.get('page') || '1', 10);
         
         // 'totalPages' is a global const defined in a <script> tag in residents/index.php
         if(typeof totalPages !== 'undefined' && currentPage < totalPages) { 
            // Manually set 'page' param and navigate
            url.searchParams.set('page', currentPage + 1);
            window.location.href = url.toString();
         }
    });
    
    gotoPageBtn.addEventListener('click', () => {
        if(gotoPageInput.value) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', gotoPageInput.value);
            window.location.href = url.toString();
        }
    });
    
    gotoPageInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') gotoPageBtn.click(); });
});