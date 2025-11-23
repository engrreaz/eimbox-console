<div id="<?=  $blockName ?>" class="mt-3">
    <div class="pt-1 pb-1 card-header d-flex justify-content-between align-items-center">
        <div class="form-check ml-3 m-0">
            <input class="form-check-input" type="checkbox" id="<?=  $blockType ?>" style="transform:scale(1.5);" <?php  $ch = $blockType . '_checked'; echo $$ch; ?>>
            <label class="form-check-label  text-dark pt-1" for="<?=  $blockType ?>">

         
                <?= $blockTitle; ?>
            </label>
        </div>
        <button class="btn btn-sm btn-outline-warning p-2 pt-2" type="button" data-bs-toggle="collapse"
            data-bs-target="#block_<?=  $index ?>">
            Show/Hide
        </button>
    </div>

    <!-- Collapsible Body -->
    <div id="block_<?=  $index ?>" class="collapse hide">
        <div class="card-body mt-0 pt-3">
            <div class="row">
                <!-- Left side -->
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">1st Priority</label>
                                <select class="form-control form-control-sm" id="sms_in_priority_1">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($$blockType[1] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($$blockType[1] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($$blockType[1] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">2nd Priority</label>
                                <select class="form-control form-control-sm" id="sms_in_priority_2">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($$blockType[2] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($$blockType[2] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($$blockType[2] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">3rd Priority</label>
                                <select class="form-control form-control-sm" id="sms_in_priority_3">
                                    <option value=""></option>
                                    <option value="on_submit" <?php echo ($$blockType[3] == 'on_submit') ? 'selected' : ''; ?>>On Submit Attnd</option>
                                    <option value="after_1st_period" <?php echo ($$blockType[3] == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?php echo ($$blockType[3] == 'on_time') ? 'selected' : ''; ?>>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class=" form-label">@ Fixed Time</label>
                                <input type="time" id="sms_in_fixed_time" class="form-control form-control-sm" value="<?php echo htmlspecialchars($$blockType[4] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-outline-success btn-sm" onclick="savesetting('<?=  $blockName ?>', '<?=  $blockType ?>');">Update
                                Setting</button>
                        </div>
                               <div class="col-md-3" id="jsondata_<?=  $blockName ?>">

                        </div>
                    </div>
                </div>

                <!-- Right side -->
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class=" form-label">Message Text</label>
                        <textarea class="form-control form-control-sm" id="sms_in_text"
                            rows="4"><?php echo htmlspecialchars($$blockType[5] ?? ''); ?></textarea>
                    </div>

                    <!-- Preview box -->
                    <div class="small form-label border rounded p-2 bg-light mb-2">
                        Dear Guardian, Labib Shahriar are at school today at 09:00:00 AM.
                    </div>

                    <button class="btn btn-inverse-primary btn-sm p-2 pt-2 mt-2" 
                    data-cat = "<?=  $blockType ?>" 
                    >
                        SMS Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>