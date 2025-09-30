// residents_table.js
// Only updates filtered results message
(() => {
    const filteredResults = document.getElementById('filteredResults');
    const filteredCount = document.getElementById('filteredCount');

    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const genderFilter = document.getElementById('genderFilter');
    const ageMin = document.getElementById('ageMin');
    const ageMax = document.getElementById('ageMax');
    const purokFilter = document.getElementById('purokFilter');
    const barangayFilter = document.getElementById('barangayFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');
    const tableBody = document.getElementById('residentsTableBody');

    const updateFilteredCount = () => {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        let count = 0;

        rows.forEach(row => {
            const name = row.cells[0]?.textContent.toLowerCase() || '';
            const address = row.cells[3]?.textContent.toLowerCase() || '';
            const age = parseInt(row.dataset.age) || 0;
            const gender = (row.dataset.gender || '').toLowerCase();
            const status = (row.dataset.status || '').toLowerCase();
            const purok = (row.dataset.purok || '').toLowerCase();
            const barangay = (row.dataset.barangay || '').toLowerCase();

            if (searchInput.value && !name.includes(searchInput.value.toLowerCase()) && !address.includes(searchInput.value.toLowerCase())) return;
            if (statusFilter.value && status !== statusFilter.value.toLowerCase()) return;
            if (genderFilter.value && gender !== genderFilter.value.toLowerCase()) return;
            if (ageMin.value && age < parseInt(ageMin.value)) return;
            if (ageMax.value && age > parseInt(ageMax.value)) return;
            if (purokFilter.value && purok !== purokFilter.value.toLowerCase()) return;
            if (barangayFilter.value && barangay !== barangayFilter.value.toLowerCase()) return;

            count++;
        });

        filteredCount.textContent = count;
        filteredResults.style.display = (count !== rows.length || searchInput.value || statusFilter.value || genderFilter.value || ageMin.value || ageMax.value || purokFilter.value || barangayFilter.value) ? 'block' : 'none';
    };

    // Listen to all filter changes
    [searchInput, statusFilter, genderFilter, ageMin, ageMax, purokFilter, barangayFilter].forEach(el => {
        el.addEventListener('input', updateFilteredCount);
        el.addEventListener('change', updateFilteredCount);
    });

    clearBtn.addEventListener('click', updateFilteredCount);

    // Initial count
    updateFilteredCount();
})();
