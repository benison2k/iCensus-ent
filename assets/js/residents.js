document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('residentModal');
    const closeModal = modal.querySelector('.close');
    const form = document.getElementById('residentForm');
    const modalTitle = document.getElementById('modalTitle');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = modal.querySelector('.editBtn');
    const deleteBtn = modal.querySelector('.deleteBtn');

    const tableBody = document.getElementById('residentsTableBody');

    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const genderFilter = document.getElementById('genderFilter');
    const ageMin = document.getElementById('ageMin');
    const ageMax = document.getElementById('ageMax');
    const purokFilter = document.getElementById('purokFilter');
    const barangayFilter = document.getElementById('barangayFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');

    const setFormEditable = (editable) => {
        form.querySelectorAll('input, select').forEach(input => input.disabled = !editable);
        saveBtn.style.display = editable ? 'inline-flex' : 'none';
    };

    // Open modal on "more" button
    const openModal = async (id) => {
        try {
            const res = await fetch(`../core/residents_process.php?action=get&resident_id=${id}`);
            const result = await res.json();
            if(result.status !== 'success') return alert('Resident not found.');
            const data = result.resident;
            Object.keys(data).forEach(key => { if(form[key]) form[key].value = data[key]; });
            setFormEditable(false);
            modalTitle.textContent = `Resident Info - ${data.first_name} ${data.last_name}`;
            modal.style.display = 'block';
            form.dataset.id = id;
        } catch(err) { console.error(err); alert('Failed to fetch resident data.'); }
    };

    document.querySelectorAll('.moreBtn').forEach(btn => btn.addEventListener('click', () => openModal(btn.dataset.id)));

    closeModal.addEventListener('click', () => { modal.style.display='none'; setFormEditable(false); form.reset(); });
    window.addEventListener('click', (e) => { if(e.target===modal){ modal.style.display='none'; setFormEditable(false); form.reset(); } });

    editBtn.addEventListener('click', () => { setFormEditable(true); modalTitle.textContent='Edit Resident'; });

    deleteBtn.addEventListener('click', async () => {
        const id = form.dataset.id;
        if(!id) return;
        if(!confirm('Are you sure you want to delete this resident?')) return;
        try {
            const res = await fetch('../core/residents_process.php?action=delete', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body: new URLSearchParams({id})
            });
            const result = await res.json();
            if(result.status==='success') fetchFilteredResidents();
            else alert('Delete failed');
        } catch(err) { console.error(err); alert('Delete failed'); }
    });

    document.getElementById('addResidentBtn').addEventListener('click', () => {
        form.reset();
        delete form.dataset.id;
        setFormEditable(true);
        modalTitle.textContent = 'Add New Resident';
        modal.style.display = 'block';
    });

    // Clear filters
    clearBtn.addEventListener('click', () => {
        searchInput.value=''; statusFilter.value=''; genderFilter.value='';
        ageMin.value=''; ageMax.value=''; purokFilter.value=''; barangayFilter.value='';
        fetchFilteredResidents();
    });

    // Fetch filtered residents via AJAX
    const fetchFilteredResidents = async () => {
        const params = new URLSearchParams({
            search: searchInput.value,
            status: statusFilter.value,
            gender: genderFilter.value,
            age_min: ageMin.value,
            age_max: ageMax.value,
            purok: purokFilter.value,
            barangay: barangayFilter.value
        });
        try {
            const res = await fetch(`../core/residents_process.php?action=filter&${params.toString()}`);
            const result = await res.json();
            if(result.status!=='success') return;
            tableBody.innerHTML='';
            result.residents.forEach(r=>{
                const middleInitial = r.middle_name ? r.middle_name[0].toUpperCase()+'.' : '';
                const fullName = `${r.first_name} ${middleInitial} ${r.last_name}`.trim();
                const address = `${r.house_no} ${r.street}, Purok ${r.purok}, ${r.barangay}`;
                tableBody.innerHTML += `<tr data-id="${r.id}" data-status="${r.status}" data-gender="${r.gender}" data-age="${r.age}" data-purok="${r.purok}" data-barangay="${r.barangay}">
                    <td>${fullName}</td>
                    <td>${r.age}</td>
                    <td>${r.gender}</td>
                    <td>${address}</td>
                    <td><span class="status-label status-${r.status.toLowerCase()}">${r.status}</span></td>
                    <td><button class="moreBtn material-icons" data-id="${r.id}" title="View Resident Info">more_vert</button></td>
                </tr>`;
            });
            document.querySelectorAll('.moreBtn').forEach(btn => btn.addEventListener('click', ()=>openModal(btn.dataset.id)));
        } catch(err){ console.error(err); }
    };

    searchInput.addEventListener('input', fetchFilteredResidents);
    statusFilter.addEventListener('change', fetchFilteredResidents);
    genderFilter.addEventListener('change', fetchFilteredResidents);
    ageMin.addEventListener('input', fetchFilteredResidents);
    ageMax.addEventListener('input', fetchFilteredResidents);
    purokFilter.addEventListener('change', fetchFilteredResidents);
    barangayFilter.addEventListener('change', fetchFilteredResidents);

    // Initial load
    fetchFilteredResidents();
});
