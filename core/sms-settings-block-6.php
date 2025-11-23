<div id="block_6">
    <div class="pt-1 pb-1 card-header d-flex justify-content-between align-items-center">
        <div class="form-check ml-3 m-0">
            <input class="form-check-input" type="checkbox" id="sms_month_report" style="transform:scale(1.5);" <?php echo $sms_month_report_checked; ?>>
            <label class="form-check-label  text-primary pt-1" for="sms_month_report">
                Monthly Report
            </label>
        </div>
        <button class="btn btn-sm btn-outline-warning  p-2 pt-2" type="button" data-bs-toggle="collapse"
            data-bs-target="#block_f">
            Show/Hide
        </button>
    </div>

    <!-- Collapsible Body -->
    <div id="block_f" class="collapse hide">
        <div class="card-body mt-0 pt-3">
            <div class="row">
                <!-- Left side -->
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">1st Priority</label>
                                <select class="form-control form-control-sm" id="sms_month_report_priority_1">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($sms_month_report[1] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($sms_month_report[1] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($sms_month_report[1] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">2nd Priority</label>
                                <select class="form-control form-control-sm" id="sms_month_report_priority_2">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($sms_month_report[2] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($sms_month_report[2] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($sms_month_report[2] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">3rd Priority</label>
                                <select class="form-control form-control-sm" id="sms_month_report_priority_3">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($sms_month_report[3] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($sms_month_report[3] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($sms_month_report[3] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">@ Fixed Time</label>
                                <input type="time" id="sms_month_report_fixed_time" class="form-control form-control-sm" value="<?php echo htmlspecialchars($sms_month_report[4] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-outline-success btn-sm " onclick="savesetting('block_6', 'sms_month_report');">Update
                                Setting</button>
                        </div>
                               <div class="col-md-3" id="jsondata_block_6">

                        </div>
                    </div>
                </div>

                <!-- Right side -->
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class=" form-label">Message Text</label>
                        <textarea class="form-control form-control-sm" id="sms_month_report_text"
                            rows="4"><?php echo htmlspecialchars($sms_month_report[5] ?? ''); ?></textarea>
                    </div>

                    <!-- Preview box -->
                    <div class="small form-label border rounded p-2 bg-light mb-2">
                        Dear Guardian, Labib Shahriar are at school today at 09:00:00 AM.
                    </div>

                    <button class="btn btn-inverse-primary btn-sm p-2 pt-2 mt-2">
                        SMS Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>