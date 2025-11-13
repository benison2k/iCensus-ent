<?php
// /app/views/components/chart_builder_modal.php
?>

<div id="chartBuilderModal" class="chart-builder-modal">
    <div class="chart-builder-content">
        <span class="close-btn" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 style="margin-top:0; margin-bottom: 25px;">Create Analytics Chart</h2>
        
        <div class="chart-builder-main-grid">
            
            <div class="chart-builder-form-container">
                <div class="template-section">
                    <h4>Start with a template:</h4>
                    <div>
                        <button type="button" class="btn-template" data-template="gender_pie">Gender</button>
                        <button type="button" class="btn-template" data-template="purok_bar">Purok</button>
                        <button type="button" class="btn-template" data-template="age_brackets">Age Brackets</button>
                        <button type="button" class="btn-template" data-template="pwd_pie">PWD</button>
                        <button type="button" class="btn-template" data-template="civil_status_pie">Civil Status</button>
                        <button type="button" class="btn-template" data-template="four_ps_pie">4Ps Members</button>
                        <button type="button" class="btn-template" data-template="education_bar">Education</button>
                        <button type="button" class="btn-template" data-template="avg_age_kpi">Avg. Age (KPI)</button>
                        <button type="button" class="btn-template" data-template="voter_pie">Voters</button>
                        <button type="button" class="btn-template" data-template="student_pie">Students</button>
                        <button type="button" class="btn-template" data-template="solo_parent_pie">Solo Parents</button>
                        <button type="button" class="btn-template" data-template="occupation_bar">Top Occupations</button>
                    </div>
                </div>

                <form id="chartBuilderForm">
                    <h4 style="margin-top:0;">Or build a custom chart:</h4>
                    <div class="chart-builder-grid">
                        <div class="chart-builder-group">
                            <label for="chartTitle">Chart Title</label>
                            <input type="text" id="chartTitle" name="title" placeholder="e.g., Seniors by Purok" required>
                        </div>
                        <div class="chart-builder-group">
                            <label for="chartType">Chart Type</label>
                            <select id="chartType" name="chart_type">
                                <option value="PieChart">Pie Chart</option>
                                <option value="DonutChart">Donut Chart</option>
                                <option value="BarChart">Bar Chart</option>
                                <option value="ColumnChart">Column Chart</option>
                                <option value="KPI">KPI (Single Number)</option>
                            </select>
                        </div>
                    </div>

                    <div class="chart-builder-grid">
                        <div class="chart-builder-group">
                            <label for="aggregateFunction">Measure</label>
                            <select id="aggregateFunction" name="aggregate_function">
                                <option value="COUNT">Count of Residents</option>
                                <option value="AVG">Average Age</option>
                            </select>
                        </div>
                        <div class="chart-builder-group">
                            <label for="groupByColumn">Group By</label>
                            <select id="groupByColumn" name="group_by_column">
                                <option value="">None (for KPIs)</option>
                                <option value="gender">Gender</option>
                                <option value="purok">Purok</option>
                                <option value="civil_status">Civil Status</option>
                                <option value="educational_attainment">Educational Attainment</option>
                                <option value="employment_status">Employment Status</option>
                                <option value="occupation">Occupation</option>
                                <option value="ownership_status">Ownership Status</option>
                                <option value="blood_type">Blood Type</option>
                                <option value="nationality">Nationality</option>
                                <option value="relationship">Relationship to Head</option>
                                <option value="residency_status">Residency Status</option>
                                <option value="status">Resident Status</option>
                                <option value="is_pwd">Is PWD?</option>
                                <option value="is_4ps_member">Is 4Ps Member?</option>
                                <option value="is_registered_voter">Is Registered Voter?</option>
                                <option value="is_solo_parent">Is Solo Parent?</option>
                                <option value="is_indigent">Is Indigent?</option>
                                <option value="dob">Age Brackets</option>
                            </select>
                        </div>
                    </div>

                    <h4>(Optional) Filter the data</h4>
                    <div id="filterContainer"></div>
                    <button type="button" id="addFilterBtn" style="padding: 8px 12px; margin-top: 10px;">+ Add Custom Filter</button>

                    <hr style="margin: 25px 0;">

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="chart-builder-group" style="flex-direction: row; align-items: center; gap: 10px;">
                            <label class="switch">
                                <input type="checkbox" id="addToDashboard" name="add_to_dashboard" value="1" checked>
                                <span class="slider round"></span>
                            </label>
                            <label for="addToDashboard" style="margin-bottom: 0; cursor: pointer;">Add to dashboard immediately</label>
                        </div>
                        <button type="submit" id="saveChartBtn" style="padding: 12px 20px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Save Chart</button>
                    </div>
                </form>
            </div>

            <div class="chart-builder-preview-container">
                <h4>Chart Preview</h4>
                <div id="chartPreview">
                    <div class="chart-placeholder">Adjust the settings on the left to see a preview.</div>
                </div>
            </div>

        </div>
    </div>
</div>