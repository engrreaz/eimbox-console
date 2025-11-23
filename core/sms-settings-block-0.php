<?php

if ($sms_setting == 1) {
    $check_0 = 'checked';
} else {
    $check_0 = '';
}
?>

<div class="row d-print-none" id="block_0">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-header">
                <h4 class="m-0">SMS Gateway.</h4>
            </div>

            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="form-check ml-3">
                    <input class="form-check-input" type="checkbox" id="sms_api" style="transform:scale(1.5);" <?php echo $check_0;?>>
                    <label class="form-check-label fw-bold text-primary pt-1" for="sms_api">
                        Message Gateway Setup
                    </label>
                </div>
                <button class="btn btn-sm btn-outline-warning  p-2 pt-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#sms_gateway">
                    Show/Hide
                </button>
            </div>


            <div id="sms_gateway" class="collapse hide">
                <div class="card-body">
                    <div class="row mt-3" id="sms-setup-block">
                        <div class="col-md-3">
                            <label for="api_key" class="form-label text-muted">API Key</label>
                            <input type="text" id="api_key" class="form-control form-control-sm"
                                value="<?php echo $sms_gateway[1]; ?>" />
                        </div>
                        <div class="col-md-3">
                            <label for="secret_key" class="form-label text-muted">Secret Key</label>
                            <input type="text" id="secret_key" class="form-control form-control-sm"
                                value="<?php echo $sms_gateway[2]; ?>" />
                        </div>
                        <div class="col-md-3">
                            <label for="username" class="form-label text-muted">Username</label>
                            <input type="text" id="username" class="form-control form-control-sm"
                                value="<?php echo $sms_gateway[3]; ?>" />
                        </div>
                        <div class="col-md-3">
                            <label for="password" class="form-label text-muted">Password</label>
                            <input type="text" id="password" class="form-control form-control-sm"
                                value="<?php echo $sms_gateway[4]; ?>" />
                        </div>
                    </div>


                    <div class="row mt-3  ">
                        <div class="col-md-12">
                            <label for="uri" class="form-label text-muted">API URL</label>
                            <input type="text" id="uri" class="form-control form-control-sm"
                                value="<?php echo $sms_gateway[5]; ?>" />
                        </div>

                    </div>
                    <div class="row mt-3  ">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-sm p-2 pt-2"
                                onclick="savesetting('block_0', 'sms_gateway');">Save Setting</button>
                        </div>
                        <div class="col-md-3" id="jsondata_block_0">

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>