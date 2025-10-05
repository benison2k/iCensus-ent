<div id="residentModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); overflow-y:auto; padding:2rem 1rem; z-index:1000;">
    <div class="modal-content" style="background:#fff; border-radius:12px; max-width:1200px; width:100%; margin:auto; padding:2rem; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; flex-direction:column; gap:1.5rem; position:relative; transition: background-color 0.4s, color 0.4s;">

        <span class="close" style="position:absolute; top:1rem; right:1rem; font-size:2rem; cursor:pointer; color:#555;">
            <span class="material-icons">close</span>
        </span>

        <h3 id="modalTitle" style="font-size:1.5rem; font-weight:600; margin-bottom:1rem; color:#333;">Resident Info</h3>

        <form id="residentForm" method="POST" action="/iCensus-ent/public/residents/process">
            <input type="hidden" name="resident_id" id="resident_id">

            <div style="display:flex; flex-wrap:wrap; gap:1rem;">

                <div style="flex:1 1 22%; min-width:220px; display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">person</span> Personal Info
                    </h4>
                    <label><span class="material-icons">person</span> First Name
                        <input type="text" name="first_name" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">badge</span> Middle Name
                        <input type="text" name="middle_name" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">badge</span> Last Name
                        <input type="text" name="last_name" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">cake</span> Date of Birth
                        <input type="date" name="dob" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">wc</span> Gender
                        <select name="gender" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </label>
                </div>

                <div style="flex:1 1 22%; min-width:220px; display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">phone</span> Contact Info
                    </h4>
                    <label><span class="material-icons">phone</span> Contact Number
                        <input type="text" name="contact_number" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">email</span> Email
                        <input type="email" name="email" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">person_add</span> Emergency Name
                        <input type="text" name="emergency_name" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">supervised_user_circle</span> Relation
                        <input type="text" name="emergency_relation" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">phone_in_talk</span> Emergency Number
                        <input type="text" name="emergency_number" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                </div>

                <div style="flex:1 1 22%; min-width:220px; display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">home</span> Address
                    </h4>
                    <label><span class="material-icons">home</span> House No.
                        <input type="number" name="house_no" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">streetview</span> Street
                        <input type="text" name="street" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">apartment</span> Purok
                        <input type="number" name="purok" required style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                     <div id="householdDetector" style="display:none; margin-top: 1rem; padding: 0.8rem; background: #f0f8ff; border-radius: 6px;">
                        <label for="householdHeadSelect" style="font-weight: 600;">Household Found!</label>
                        <p style="font-size: 0.9em; margin: 0.2rem 0;">We found other residents at this address. You can select the Head of Household from the list below.</p>
                        <select id="householdHeadSelect" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc; margin-top:0.5rem;">
                            <option value="">Select Head of Household</option>
                        </select>
                    </div>
                </div>
                
                <div style="flex:1 1 22%; min-width:220px; display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">family_restroom</span> Household Info
                    </h4>
                    <label><span class="material-icons">family_restroom</span> Head of Household
                        <input type="text" name="head_of_household" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                    <label><span class="material-icons">group</span> Relationship to Head
                        <input type="text" name="relationship" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                    </label>
                </div>

                <div style="flex:1 1 22%; min-width:220px; display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">assignment_ind</span> Residency Info
                    </h4>
                    <label><span class="material-icons">assignment_ind</span> Status
                        <select name="status" style="width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Moved">Moved</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                    </label>
                </div>

            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:1.5rem;">
                <button type="button" class="editBtn" style="padding:0.5rem 1rem; border-radius:6px; border:none; cursor:pointer; background:#2196f3; color:#fff;"><span class="material-icons">edit</span> Edit</button>
                <button type="button" class="deleteBtn" style="padding:0.5rem 1rem; border-radius:6px; border:none; cursor:pointer; background:#f44336; color:#fff;"><span class="material-icons">delete</span> Delete</button>
                <button type="submit" id="saveBtn" style="display:none; padding:0.5rem 1rem; border-radius:6px; border:none; cursor:pointer; background:#4caf50; color:#fff;"><span class="material-icons">save</span> Save</button>
            </div>

        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('residentModal');
    if (!modal) return;
    
    const closeBtn = modal.querySelector('.close');
    const body = document.body;
    const basePath = '/iCensus-ent/public';

    function openModal() { modal.style.display = 'block'; }
    function closeModal() { modal.style.display = 'none'; }

    if(closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    
    window.addEventListener('click', e => { if(e.target === modal) closeModal(); });

    // --- START: NEW VALIDATION & HOUSEHOLD LOGIC ---
    const houseNoInput = modal.querySelector('input[name="house_no"]');
    const streetInput = modal.querySelector('input[name="street"]');
    const purokInput = modal.querySelector('input[name="purok"]');
    const householdDetector = document.getElementById('householdDetector');
    const householdHeadSelect = document.getElementById('householdHeadSelect');
    const headOfHouseholdInput = modal.querySelector('input[name="head_of_household"]');

    // Allow only numbers for House No and Purok
    [houseNoInput, purokInput].forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            checkForHousehold();
        });
    });

    // Allow only letters and spaces for Street
    streetInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        checkForHousehold();
    });
    
    const checkForHousehold = async () => {
        const houseNo = houseNoInput.value.trim();
        const street = streetInput.value.trim();
        const purok = purokInput.value.trim();

        if (houseNo && street && purok) {
            const response = await fetch(`${basePath}/residents/find-by-address?house_no=${houseNo}&street=${street}&purok=${purok}`);
            const residents = await response.json();
            
            if (residents.length > 0) {
                householdDetector.style.display = 'block';
                householdHeadSelect.innerHTML = '<option value="">Select Head of Household</option>';
                residents.forEach(resident => {
                    const fullName = `${resident.first_name} ${resident.last_name}`;
                    const option = new Option(fullName, fullName);
                    if(resident.relationship === 'Self') {
                        option.selected = true;
                        headOfHouseholdInput.value = fullName;
                    }
                    householdHeadSelect.add(option);
                });
            } else {
                householdDetector.style.display = 'none';
            }
        } else {
            householdDetector.style.display = 'none';
        }
    };
    
    householdHeadSelect.addEventListener('change', function() {
        headOfHouseholdInput.value = this.value;
    });
    // --- END: NEW LOGIC ---

    // Function to apply dark mode styles
    function applyDarkModeStyles() {
        const isDarkMode = body.classList.contains('dark-mode');
        const content = modal.querySelector('.modal-content');
        const headers = modal.querySelectorAll('.modal-header-text');
        const inputs = modal.querySelectorAll('input, select');
        const labels = modal.querySelectorAll('label');
        const modalTitle = modal.querySelector('#modalTitle');

        if (isDarkMode) {
            content.style.backgroundColor = '#2C3E50';
            content.style.color = '#fff';
            if(modalTitle) modalTitle.style.color = '#fff';
            
            headers.forEach(h => h.style.color = '#fff');
            
            inputs.forEach(i => {
                i.style.backgroundColor = '#1e1e2f';
                i.style.color = '#fff';
                i.style.border = '1px solid #555';
            });
            
            labels.forEach(l => l.style.color = '#fff');

        } else {
            content.style.backgroundColor = '#fff';
            content.style.color = '#333';
             if(modalTitle) modalTitle.style.color = '#333';

            headers.forEach(h => h.style.color = '#444');

            inputs.forEach(i => {
                i.style.backgroundColor = '#fff';
                i.style.color = '#333';
                i.style.border = '1px solid #ccc';
            });

            labels.forEach(l => l.style.color = '#333');
        }
    }

    // Apply styles on initial load
    applyDarkModeStyles();

    // Use a MutationObserver to watch for class changes on the body
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.attributeName === 'class') {
                applyDarkModeStyles();
            }
        });
    });

    observer.observe(body, {
        attributes: true
    });
});
</script>