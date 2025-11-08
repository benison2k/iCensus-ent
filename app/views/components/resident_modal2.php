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