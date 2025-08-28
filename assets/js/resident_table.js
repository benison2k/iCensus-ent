document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('residentsTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));

    const pageSizeSelect = document.getElementById('pageSizeSelect');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');

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

    let currentPage = 1;
    let pageSize = parseInt(pageSizeSelect.value);
    let filteredRows = [...rows];

    const applyFilters = () => {
        filteredRows = rows.filter(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const age = parseInt(row.dataset.age);
            const gender = row.dataset.gender;
            const status = row.dataset.status;
            const purok = row.dataset.purok;
            const barangay = row.dataset.barangay;
            const search = searchInput.value.toLowerCase();

            if (search && !name.includes(search) && !row.cells[3].textContent.toLowerCase().includes(search)) return false;
            if (statusFilter.value && status !== statusFilter.value) return false;
            if (genderFilter.value && gender !== genderFilter.value) return false;
            if (ageMin.value && age < parseInt(ageMin.value)) return false;
            if (ageMax.value && age > parseInt(ageMax.value)) return false;
            if (purokFilter.value && purok !== purokFilter.value) return false;
            if (barangayFilter.value && barangay !== barangayFilter.value) return false;

            return true;
        });

        filteredCount.textContent = filteredRows.length;
        filteredResults.style.display = (
            filteredRows.length !== rows.length ||
            searchInput.value ||
            statusFilter.value ||
            genderFilter.value ||
            ageMin.value ||
            ageMax.value ||
            purokFilter.value ||
            barangayFilter.value
        ) ? 'block' : 'none';

        currentPage = 1;
        renderTable();
    };

    const renderTable = () => {
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;

        filteredRows.forEach((row, idx) => {
            row.style.display = (idx >= start && idx < end) ? '' : 'none';
        });

        const totalPages = Math.ceil(filteredRows.length / pageSize) || 1;
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;
    };

    // Event listeners
    pageSizeSelect.addEventListener('change', () => {
        pageSize = parseInt(pageSizeSelect.value);
        currentPage = 1;
        renderTable();
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) currentPage--;
        renderTable();
    });

    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / pageSize);
        if (currentPage < totalPages) currentPage++;
        renderTable();
    });

    [searchInput, statusFilter, genderFilter, ageMin, ageMax, purokFilter, barangayFilter].forEach(el => el.addEventListener('input', applyFilters));

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        statusFilter.value = '';
        genderFilter.value = '';
        ageMin.value = '';
        ageMax.value = '';
        purokFilter.value = '';
        barangayFilter.value = '';
        applyFilters();
    });

    applyFilters();
});
