<style>
/* Modern Modal Styles */
.modal-modern {
    display: none;
    position: fixed; 
    z-index: 1000; 
    left: 0; 
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: hidden;
    background: rgba(0,0,0,0.6); 
    backdrop-filter: blur(5px);
    align-items: center;
    justify-content: center;
}
.modal-modern-content {
    background: #fff;
    border-radius: 16px;
    width: 95%;
    max-width: 1000px;
    height: 90vh;
    max-height: 700px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: fadeIn 0.3s ease-out;
    overflow: hidden;
}
.modal-modern-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e0e0e0;
    flex-shrink: 0;
}
.modal-modern-header h3 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 600;
}

/* --- Progress Bar Styles --- */
.progress-container {
    width: 100%;
    background-color: #e0e0e0;
    border-radius: 8px;
    margin: 0.5rem 0 1.5rem 0;
}
.progress-bar {
    width: 0%;
    height: 20px;
    background: linear-gradient(90deg, #4caf50, #81c784);
    text-align: center;
    line-height: 20px;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    border-radius: 8px;
    transition: width 0.4s ease-out;
}

.modal-modern-body {
    display: flex;
    flex-grow: 1;
    min-height: 0;
}
.modal-tabs {
    flex: 0 0 200px;
    border-right: 1px solid #e0e0e0;
    padding: 1.5rem 0;
}
.tab-button {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.8rem 1.5rem;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    color: #555;
    border-right: 3px solid transparent;
    transition: all 0.2s ease;
}
.tab-button:hover { background-color: #f4f6f8; }
.tab-button.active {
    background-color: #e3f2fd;
    color: #0d6efd;
    font-weight: 600;
    border-right-color: #0d6efd;
}
.tab-content {
    display: none;
    padding: 0 2rem 1.5rem 2rem;
    overflow-y: auto;
    flex-grow: 1;
}
.tab-content.active { display: block; }
.tab-content h4 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    color: #333;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.5rem;
}
.form-group { display: flex; flex-direction: column; }
.form-group label { margin-bottom: 0.3rem; font-size: 0.85rem; font-weight: 500; color: #666; }
.form-group label .required-asterisk { color: #dc3545; font-weight: 700; margin-left: 2px; }
.form-group input, .form-group select {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
}
.form-group.full-width { grid-column: 1 / -1; }
.modal-modern-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e0e0e0;
    flex-shrink: 0;
}
.modal-footer-btn { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
.btn-edit { background-color: #2196f3; color: #fff; }
.btn-delete { background-color: #f44336; color: #fff; }
.btn-save { background-color: #4caf50; color: #fff; }

/* Dark Mode */
body.dark-mode .modal-modern-content { background: #2C3E50; }
body.dark-mode .progress-container { background-color: #1e1e2f; }
body.dark-mode .modal-modern-header, body.dark-mode .modal-tabs, body.dark-mode .modal-modern-footer { border-color: #4a5a6a; }
body.dark-mode .modal-modern-header h3 { color: #fff; }
body.dark-mode .tab-button { color: #ccc; }
body.dark-mode .tab-button:hover { background-color: #34495e; }
body.dark-mode .tab-button.active { background-color: #1e1e2f; border-right-color: #4da3ff; color: #4da3ff; }
body.dark-mode .tab-content h4 { color: #fff; }
body.dark-mode .form-group label { color: #bbb; }
body.dark-mode .form-group input, body.dark-mode .form-group select { background-color: #1e1e2f; border-color: #555; color: #fff; }

@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>

<div id="residentModal" class="modal modal-modern">
    <div class="modal-modern-content">
        <div class="modal-modern-header">
            <h3 id="modalTitle">Resident Information</h3>
            <span class="close" style="font-size:1.8rem; cursor:pointer;"><span class="material-icons">close</span></span>
        </div>
        <form id="residentForm" method="POST" action="/iCensus-ent/public/residents/process" style="display: contents;">
            <div class="modal-modern-body">
                <div class="modal-tabs">
                    <button type="button" class="tab-button active" data-tab="personal"><span class="material-icons">person</span> Personal</button>
                    <button type="button" class="tab-button" data-tab="household"><span class="material-icons">home</span> Household</button>
                    <button type="button" class="tab-button" data-tab="contact"><span class="material-icons">contact_phone</span> Contact</button>
                    <button type="button" class="tab-button" data-tab="other"><span class="material-icons">assignment</span> Other Info</button>
                </div>

                <div class="modal-form-area" style="padding-top: 1.5rem;">
                    <input type="hidden" name="resident_id" id="resident_id">

                    <div style="padding: 0 2rem;">
                        <p class="progress-label">Required Information Completeness:</p>
                        <div class="progress-container">
                            <div class="progress-bar" id="formProgressBar">0%</div>
                        </div>
                        <p style="font-size: 0.8rem; text-align: right; margin-top: -1rem; margin-bottom: 1rem; color: #666;">Fields marked with <span class="required-asterisk">*</span> are required.</p>
                    </div>
                    
                    <div id="tab-personal" class="tab-content active">
                        <h4>Personal Details</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>First Name<span class="required-asterisk">*</span></label><input type="text" name="first_name" required></div>
                            <div class="form-group"><label>Last Name<span class="required-asterisk">*</span></label><input type="text" name="last_name" required></div>
                            <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name"></div>
                            <div class="form-group"><label>Suffix</label><input type="text" name="suffix"></div>
                            <div class="form-group"><label>Date of Birth<span class="required-asterisk">*</span></label><input type="date" name="dob" required></div>
                            <div class="form-group"><label>Gender<span class="required-asterisk">*</span></label><select name="gender" required><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                            <div class="form-group"><label>Civil Status</label><select name="civil_status"><option value="">Select</option><option value="Single">Single</option><option value="Married">Married</option><option value="Widowed">Widowed</option><option value="Separated">Separated</option></select></div>
                            <div class="form-group"><label>Nationality</label><input type="text" name="nationality" value="Filipino"></div>
                        </div>
                    </div>
                    
                    <div id="tab-household" class="tab-content">
                        <h4>Address & Household</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>House No.<span class="required-asterisk">*</span></label><input type="number" name="house_no" required></div>
                            <div class="form-group"><label>Purok<span class="required-asterisk">*</span></label><input type="number" name="purok" required></div>
                            <div class="form-group full-width"><label>Street<span class="required-asterisk">*</span></label><input type="text" name="street" required></div>
                            <div class="form-group"><label>Household No.</label><input type="text" name="household_no" placeholder="e.g., FAM-001"></div>
                            <div class="form-group"><label>Ownership Status</label><select name="ownership_status"><option value="">Select</option><option value="Owned">Owned</option><option value="Rented">Rented</option><option value="Living with Relatives">Living with Relatives</option></select></div>
                            <div class="form-group"><label>Head of Household</label><input type="text" name="head_of_household"></div>
                            <div class="form-group"><label>Relationship to Head</label><input type="text" name="relationship"></div>
                        </div>
                    </div>

                    <div id="tab-contact" class="tab-content">
                        <h4>Contact & Health</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number"></div>
                            <div class="form-group"><label>Email Address</label><input type="email" name="email"></div>
                            <div class="form-group"><label>PhilHealth No.</label><input type="text" name="philhealth_no"></div>
                            <div class="form-group"><label>Blood Type</label><input type="text" name="blood_type"></div>
                            <hr style="grid-column: 1 / -1; border: 0; border-top: 1px solid #e0e0e0; margin: 0.5rem 0;">
                            <div class="form-group"><label>Emergency Contact Name</label><input type="text" name="emergency_name"></div>
                            <div class="form-group"><label>Emergency Contact Number</label><input type="text" name="emergency_number"></div>
                            <div class="form-group full-width"><label>Relation to Emergency Contact</label><input type="text" name="emergency_relation"></div>
                        </div>
                    </div>

                    <div id="tab-other" class="tab-content">
                        <h4>Administrative & Other Info</h4>
                        <div class="form-grid">
                            <div class="form-group"><label>Educational Attainment</label><select name="educational_attainment"><option value="">Select</option><option value="No Formal Education">No Formal Education</option><option value="Pre-school">Pre-school</option><option value="Elementary Level">Elementary Level</option><option value="Elementary Graduate">Elementary Graduate</option><option value="High School Level">High School Level</option><option value="High School Graduate">High School Graduate</option><option value="Vocational Graduate">Vocational Graduate</option><option value="College Level">College Level</option><option value="College Graduate">College Graduate</option><option value="Doctorate Degree">Doctorate Degree</option></select></div>
                            <div class="form-group"><label>Occupation</label><input type="text" name="occupation"></div>
                            <div class="form-group"><label>Status</label><select name="status"><option value="Active">Active</option><option value="Inactive">Inactive</option><option value="Moved">Moved</option><option value="Deceased">Deceased</option></select></div>
                            <div class="form-group"><label>Registered Voter</label><select name="is_registered_voter"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>PWD</label><select name="is_pwd"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>Solo Parent</label><select name="is_solo_parent"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>Indigent</label><select name="is_indigent"><option value="0">No</option><option value="1">Yes</option></select></div>
                            <div class="form-group"><label>4Ps Member</label><select name="is_4ps_member"><option value="0">No</option><option value="1">Yes</option></select></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-modern-footer">
                <a id="approveBtn" href="#" class="modal-footer-btn" style="display: none; background-color: #28a745; color: white;"><span class="material-icons">check</span> Approve</a>
                <a id="declineBtn" href="#" class="modal-footer-btn" style="display: none; background-color: #dc3545; color: white;"><span class="material-icons">close</span> Decline</a>
                <button type="button" class="modal-footer-btn btn-edit editBtn"><span class="material-icons">edit</span> Edit</button>
                <button type="button" class="modal-footer-btn btn-delete deleteBtn"><span class="material-icons">delete</span> Delete</button>
                <button type="submit" id="saveBtn" class="modal-footer-btn btn-save" style="display:none;"><span class="material-icons">save</span> Save</button>
            </div>
        </form>
    </div>
</div>

<script>
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

    function updateProgress() {
        if (!progressBar) return;
        let completedCount = 0;
        requiredFields.forEach(field => {
            if (field.value.trim() !== '') {
                completedCount++;
            }
        });
        const percentage = totalRequired > 0 ? (completedCount / totalRequired) * 100 : 0;
        progressBar.style.width = percentage + '%';
        progressBar.textContent = `${Math.round(percentage)}% (${completedCount} of ${totalRequired} completed)`;
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

    modal.updateProgress = updateProgress;
});
</script>