<?php
$is_gw_enabled = !empty($sms_gateway['enabled']) || ($sms_gateway[0] ?? 0) == 1;
$check_0 = $is_gw_enabled ? 'checked' : '';

$api_key = htmlspecialchars($sms_gateway['api_key'] ?? ($sms_gateway[1] ?? ''));
$secret_key = htmlspecialchars($sms_gateway['secret_key'] ?? ($sms_gateway[2] ?? ''));
$username = htmlspecialchars($sms_gateway['username'] ?? ($sms_gateway[3] ?? ''));
$password = htmlspecialchars($sms_gateway['password'] ?? ($sms_gateway[4] ?? ''));
$uri = htmlspecialchars($sms_gateway['uri'] ?? ($sms_gateway[5] ?? ''));
$provider = $sms_gateway['provider'] ?? ($sms_gateway[6] ?? 'bulksmsbd');
$price = htmlspecialchars((string)($sms_gateway['price'] ?? ($sms_gateway[7] ?? '0.35')));
$sandbox_mode = intval($sms_gateway['sandbox_mode'] ?? 0);
?>

<div class="row d-print-none" id="block_0">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" id="enabled" style="transform:scale(1.4);" <?php echo $check_0; ?>>
                    <label class="form-check-label fw-bold text-primary pt-0 ps-2" for="enabled">
                        <i class="bi bi-broadcast me-1"></i> SMS Gateway Configuration
                    </label>
                </div>
                <div>
                    <?php if ($sandbox_mode == 1): ?>
                        <span class="badge bg-warning text-dark me-2"><i class="bi bi-bug"></i> Sandbox Mode ON (০ টাকা খরচ)</span>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#sms_gateway_collapse">
                        <i class="bi bi-sliders"></i> Show/Hide Setup
                    </button>
                </div>
            </div>

            <div id="sms_gateway_collapse" class="collapse show">
                <div class="card-body">
                    <div class="row g-3" id="sms-setup-block">
                        <div class="col-md-3">
                            <label for="api_key" class="form-label text-muted small fw-semibold">API Key / Token</label>
                            <input type="text" id="api_key" class="form-control form-control-sm" value="<?php echo $api_key; ?>" placeholder="API Key" />
                        </div>
                        <div class="col-md-3">
                            <label for="secret_key" class="form-label text-muted small fw-semibold">Secret Key (If any)</label>
                            <input type="text" id="secret_key" class="form-control form-control-sm" value="<?php echo $secret_key; ?>" placeholder="Secret Key" />
                        </div>
                        <div class="col-md-3">
                            <label for="username" class="form-label text-muted small fw-semibold">Sender ID / Username</label>
                            <input type="text" id="username" class="form-control form-control-sm" value="<?php echo $username; ?>" placeholder="Sender ID" />
                        </div>
                        <div class="col-md-3">
                            <label for="password" class="form-label text-muted small fw-semibold">Password (If required)</label>
                            <input type="password" id="password" class="form-control form-control-sm" value="<?php echo $password; ?>" placeholder="Password" />
                        </div>

                        <div class="col-md-6">
                            <label for="uri" class="form-label text-muted small fw-semibold">API Endpoint URL</label>
                            <input type="text" id="uri" class="form-control form-control-sm" value="<?php echo $uri; ?>" placeholder="http://bulksmsbd.net/api/smsapi" />
                        </div>

                        <div class="col-md-2">
                            <label for="provider" class="form-label text-muted small fw-semibold">Gateway Type</label>
                            <select class="form-select form-select-sm" id="provider">
                                <option value="bulksmsbd" <?= $provider === 'bulksmsbd' ? 'selected' : '' ?>>BulkSMSBD</option>
                                <option value="smsvaults" <?= $provider === 'smsvaults' ? 'selected' : '' ?>>SMSVaults</option>
                                <option value="sslwireless" <?= $provider === 'sslwireless' ? 'selected' : '' ?>>SSL Wireless</option>
                                <option value="greenweb" <?= $provider === 'greenweb' ? 'selected' : '' ?>>Greenweb</option>
                                <option value="self" <?= $provider === 'self' ? 'selected' : '' ?>>Self Hosted</option>
                                <option value="eimbox" <?= $provider === 'eimbox' ? 'selected' : '' ?>>EIMBox Central</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="price" class="form-label text-muted small fw-semibold">Cost / SMS (BDT)</label>
                            <input type="number" step="0.01" id="price" class="form-control form-control-sm" value="<?php echo $price; ?>" />
                        </div>

                        <div class="col-md-2">
                            <label for="sandbox_mode" class="form-label text-muted small fw-semibold">Testing Sandbox</label>
                            <select class="form-select form-select-sm border-warning" id="sandbox_mode">
                                <option value="0" <?= $sandbox_mode == 0 ? 'selected' : '' ?>>Live Mode (Real SMS)</option>
                                <option value="1" <?= $sandbox_mode == 1 ? 'selected' : '' ?>>Sandbox (Mock - 0 BDT)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4 align-items-center">
                        <div class="col-md-4">
                            <button class="btn btn-primary btn-sm px-3" onclick="savesetting('block_0', 'sms_gateway');">
                                <i class="bi bi-save me-1"></i> Save Gateway Settings
                            </button>
                        </div>
                        <div class="col-md-8" id="jsondata_block_0"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>