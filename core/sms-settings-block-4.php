<div id="block_4">
    <div class="pt-1 pb-1 card-header d-flex justify-content-between align-items-center">
        <div class="form-check ml-3 m-0">
            <input class="form-check-input" type="checkbox" id="sms_payment" style="transform:scale(1.5);" <?php echo $sms_payment_checked; ?>>
            <label class="form-check-label  text-primary pt-1" for="sms_payment">
                Collect Payment
            </label>
        </div>
        <button class="btn btn-sm btn-outline-warning  p-2 pt-2" type="button" data-bs-toggle="collapse"
            data-bs-target="#block_d">
            Show/Hide
        </button>
    </div>

    <!-- Collapsible Body -->
    <div id="block_d" class="collapse hide">
        <div class="card-body mt-0 pt-3">
            <div class="row">
                <!-- Left side -->
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">1st Priority</label>
                                <select class="form-control form-control-sm" id="sms_payment_priority_1">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($sms_payment[1] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($sms_payment[1] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($sms_payment[1] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">2nd Priority</label>
                                <select class="form-control form-control-sm" id="sms_payment_priority_2">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($sms_payment[2] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($sms_payment[2] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($sms_payment[2] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">3rd Priority</label>
                                <select class="form-control form-control-sm" id="sms_payment_priority_3">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($sms_payment[3] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($sms_payment[3] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($sms_payment[3] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">@ Fixed Time</label>
                                <input type="time" id="sms_payment_fixed_time" class="form-control form-control-sm" value="<?php echo htmlspecialchars($sms_payment[4] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-outline-success btn-sm" onclick="savesetting('block_4', 'sms_payment');">Update
                                Setting</button>
                        </div>
                               <div class="col-md-3" id="jsondata_block_4">

                        </div>
                    </div>
                </div>

                <!-- Right side -->
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class=" form-label">Message Text</label>
                        <textarea class="form-control form-control-sm" id="sms_payment_text"
                            rows="4"><?php echo htmlspecialchars($sms_payment[5] ?? ''); ?></textarea>
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