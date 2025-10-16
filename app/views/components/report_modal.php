<div id="report-modal" class="modal">
    <div class="modal-content wide">
        <span class="close-btn material-icons">close</span>
        <h2 class="modal-title">Generate Custom Report</h2>
        <form action="<?= $base_url ?>/analytics/report" method="POST" target="_blank">
            <div class="modal-form-grid">
                <div class="modal-form-column">
                    <fieldset>
                        <legend><span class="material-icons">settings</span>Options</legend>
                        <div class="form-group">
                            <label for="sort_by">Sort Data By</label>
                            <select name="sort_by" id="sort_by">
                                <option value="last_name">Last Name</option>
                                <option value="first_name">First Name</option>
                                <option value="date_added">Date Added</option>
                                <option value="dob">Date of Birth</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <select name="sort_order" id="sort_order">
                                <option value="ASC">Ascending</option>
                                <option value="DESC">Descending</option>
                            </select>
                        </div>
                    </fieldset>
                </div>

                <div class="modal-form-column">
                    <fieldset>
                        <legend><span class="material-icons">view_column</span>Data Columns</legend>
                        <p class="fieldset-subtitle">Select which data columns to include in the report table.</p>
                        <div class="checkbox-group">
                            <?php foreach ($available_columns as $key => $label): ?>
                                <label>
                                    <input type="checkbox" name="columns[]" value="<?= $key ?>" checked>
                                    <?= htmlspecialchars($label) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                </div>

                <div class="modal-form-column">
                    <fieldset>
                        <legend><span class="material-icons">pie_chart</span>Visual Charts</legend>
                        <p class="fieldset-subtitle">Select charts to include at the end of the report.</p>
                        <div class="checkbox-group">
                            <?php if (isset($user_charts) && !empty($user_charts)): ?>
                                <?php foreach ($user_charts as $chart): ?>
                                    <label>
                                        <input type="checkbox" name="charts[]" value="<?= $chart['id'] ?>">
                                        <?= htmlspecialchars($chart['title']) ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No saved charts found.</p>
                            <?php endif; ?>
                            </div>
                    </fieldset>
                </div>
            </div>
            <button type="submit" class="btn-generate">Generate Report</button>
        </form>
    </div>
</div>