<div id="residentModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); overflow-y:auto; padding:2rem 1rem; z-index:1000;">
    <div class="modal-content" style="background:#fff; border-radius:12px; max-width:1200px; width:100%; margin:auto; padding:2rem; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; flex-direction:column; gap:1.5rem; position:relative; transition: background-color 0.4s, color 0.4s;">

        <span class="close" style="position:absolute; top:1rem; right:1rem; font-size:2rem; cursor:pointer; color:#555;">
            <span class="material-icons">close</span>
        </span>

        <h3 id="modalTitle" style="font-size:1.5rem; font-weight:600; margin-bottom:1rem; color:#333;">Resident Info</h3>

        <form id="residentForm" method="POST" action="/iCensus-ent/public/residents/process">
            <input type="hidden" name="resident_id" id="resident_id">

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">

                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">person</span> Personal Info
                    </h4>
                    <label>First Name <input type="text" name="first_name" required></label>
                    <label>Middle Name <input type="text" name="middle_name"></label>
                    <label>Last Name <input type="text" name="last_name" required></label>
                    <label>Suffix <input type="text" name="suffix"></label>
                    <label>Date of Birth <input type="date" name="dob" required></label>
                    <label>Gender
                        <select name="gender" required>
                            <option value="">Select</option> <option value="Male">Male</option> <option value="Female">Female</option>
                        </select>
                    </label>
                    <label>Civil Status
                        <select name="civil_status">
                            <option value="">Select</option><option value="Single">Single</option><option value="Married">Married</option><option value="Widowed">Widowed</option><option value="Separated">Separated</option>
                        </select>
                    </label>
                </div>

                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                     <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">home</span> Address & Household
                    </h4>
                    <label>House No. <input type="number" name="house_no" required></label>
                    <label>Street <input type="text" name="street" required></label>
                    <label>Purok <input type="number" name="purok" required></label>
                    <label>Household No. <input type="text" name="household_no" placeholder="e.g., FAM-001"></label>
                     <label>Ownership Status
                        <select name="ownership_status">
                            <option value="">Select</option><option value="Owned">Owned</option><option value="Rented">Rented</option><option value="Living with Relatives">Living with Relatives</option>
                        </select>
                    </label>
                    <label>Head of Household <input type="text" name="head_of_household"></label>
                    <label>Relationship to Head <input type="text" name="relationship"></label>
                </div>

                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">contact_phone</span> Contact & Health
                    </h4>
                    <label>Contact Number <input type="text" name="contact_number"></label>
                    <label>Email <input type="email" name="email"></label>
                    <label>PhilHealth No. <input type="text" name="philhealth_no"></label>
                    <label>Blood Type <input type="text" name="blood_type"></label>
                    <label>Emergency Name <input type="text" name="emergency_name"></label>
                    <label>Emergency Relation <input type="text" name="emergency_relation"></label>
                    <label>Emergency Number <input type="text" name="emergency_number"></label>
                </div>

                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">work</span> Education & Occupation
                    </h4>
                    <label>Educational Attainment
                        <select name="educational_attainment">
                            <option value="">Select</option>
                            <option value="No Formal Education">No Formal Education</option>
                            <option value="Pre-school">Pre-school</option>
                            <option value="Elementary Level">Elementary Level</option>
                            <option value="Elementary Graduate">Elementary Graduate</option>
                            <option value="High School Level">High School Level</option>
                            <option value="High School Graduate">High School Graduate</option>
                            <option value="Vocational Graduate">Vocational Graduate</option>
                            <option value="College Level">College Level</option>
                            <option value="College Graduate">College Graduate</option>
                            <option value="Doctorate Degree">Doctorate Degree</option>
                        </select>
                    </label>
                    <label>Occupation <input type="text" name="occupation"></label>
                    <label>Nationality <input type="text" name="nationality" value="Filipino"></label>
                </div>

                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                    <h4 class="modal-header-text" style="border-bottom:1px solid #ddd; padding-bottom:0.3rem; color:#444;">
                        <span class="material-icons" style="font-size:18px; vertical-align:middle;">admin_panel_settings</span> Administrative
                    </h4>
                    <label>Status
                        <select name="status">
                            <option value="Active">Active</option> <option value="Inactive">Inactive</option> <option value="Moved">Moved</option> <option value="Deceased">Deceased</option>
                        </select>
                    </label>
                     <div class="checkbox-group" style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <label style="flex-direction: row; align-items: center;"><input type="hidden" name="is_registered_voter" value="0"><input type="checkbox" name="is_registered_voter" value="1" style="width: auto;"> Registered Voter</label>
                        <label style="flex-direction: row; align-items: center;"><input type="hidden" name="is_pwd" value="0"><input type="checkbox" name="is_pwd" value="1" style="width: auto;"> PWD</label>
                        <label style="flex-direction: row; align-items: center;"><input type="hidden" name="is_solo_parent" value="0"><input type="checkbox" name="is_solo_parent" value="1" style="width: auto;"> Solo Parent</label>
                        <label style="flex-direction: row; align-items: center;"><input type="hidden" name="is_indigent" value="0"><input type="checkbox" name="is_indigent" value="1" style="width: auto;"> Indigent</label>
                        <label style="flex-direction: row; align-items: center;"><input type="hidden" name="is_4ps_member" value="0"><input type="checkbox" name="is_4ps_member" value="1" style="width: auto;"> 4Ps Member</label>
                    </div>
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
<style>
    /* Simple styling for the form elements inside the modal */
    #residentModal label { display: flex; flex-direction: column; font-size: 0.9rem; gap: 0.2rem; }
    #residentModal input, #residentModal select { width:100%; padding:0.4rem 0.6rem; border-radius:6px; border:1px solid #ccc; }
</style>
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
    const checkHouseholdBtn = document.getElementById('checkHouseholdBtn');

    // This check is important to prevent errors if the button is not on the page
    if (checkHouseholdBtn) {
        // Allow only numbers for House No and Purok
        [houseNoInput, purokInput].forEach(input => {
            if(input) input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });
        });

        // Allow only letters and spaces for Street
        if(streetInput) streetInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        });
        
        checkHouseholdBtn.addEventListener('click', async () => {
            const houseNo = houseNoInput.value.trim();
            const street = streetInput.value.trim();
            const purok = purokInput.value.trim();

            if (houseNo && street && purok) {
                const response = await fetch(`${basePath}/residents/find-by-address?house_no=${houseNo}&street=${street}&purok=${purok}`);
                const residents = await response.json();
                
                if(householdDetector) householdDetector.style.display = 'block';
                if(householdHeadSelect) householdHeadSelect.innerHTML = '<option value="">Select Head of Household</option>'; // Reset
                
                if (residents.length > 0) {
                    let foundHead = false;
                    residents.forEach(resident => {
                        const fullName = `${resident.first_name} ${resident.last_name}`;
                        const option = new Option(fullName, fullName);
                        if(householdHeadSelect) householdHeadSelect.add(option);
                        if(resident.relationship === 'Self') {
                            option.selected = true;
                            if(headOfHouseholdInput) headOfHouseholdInput.value = fullName;
                            foundHead = true;
                        }
                    });
                    if (!foundHead && headOfHouseholdInput) {
                         headOfHouseholdInput.value = '';
                    }
                } else {
                     if(householdHeadSelect) householdHeadSelect.innerHTML += '<option value="" disabled>No residents found at this address.</option>';
                     if(headOfHouseholdInput) headOfHouseholdInput.value = '';
                }
            } else {
                alert('Please fill in the House No., Street, and Purok fields first.');
                if(householdDetector) householdDetector.style.display = 'none';
            }
        });
        
        if(householdHeadSelect) householdHeadSelect.addEventListener('change', function() {
            if(headOfHouseholdInput) headOfHouseholdInput.value = this.value;
        });
    }
    // --- END: NEW LOGIC ---

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

    applyDarkModeStyles();

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