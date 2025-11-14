<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-3">💳 Payment Gateway Settings</h4>

    <?php
    $gateway_list = ['bkash', 'nagad', 'rocket', 'bank'];
    $existing_data = $admin_data['settings']['payment_gateway'];
    ?>

    <div class="row" id="gatewayContainer">
        <?php
        $gw = [
            "gateway" => $gw_name,
            "active" => 0,
            "type" => "",
            "app_key" => "",
            "app_secret" => "",
            "username" => "",
            "password" => ""
        ];

        foreach ($gateway_list as $gw_name):
            // যদি আগের ডেটা থাকে তাহলে override করবে
            foreach ($existing_data as $item) {
                if ($item['gateway'] == $gw_name) {
                    $gw = array_merge($gw, $item);
                    break;
                }
            }
            ?>


            <div class="col-3 mb-3">
                <div class="card p-3 shadow-sm">


                    <h5 class="mb-3 text-primary">
                        <img src="assets/images/<?= $gw_name; ?>_payment_logo.png" style="height:24px; margin-right:6px;">
                        <?= ucfirst($gw_name); ?> Gateway
                    </h5>
                    <div class="mb-2">
                        <input type="text" class="form-control gateway-name" value="<?= $gw['gateway']; ?>" readonly>

                    </div>

                    <div class="mb-2">
                        <label>Activessss</label>
                        <select class="form-select gateway-active">
                            <option value="1" <?= ($gw['active'] == 1 ? 'selected' : ''); ?>>Active</option>
                            <option value="0" <?= ($gw['active'] == 0 ? 'selected' : ''); ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Type</label>
                        <input type="text" class="form-control gateway-type" value="<?= $gw['type'] ?>">
                    </div>

                    <div class="mb-2">
                        <label>App Key</label>
                        <input type="text" class="form-control gateway-key" value="<?= $gw['app_key'] ?>">
                    </div>

                    <div class="mb-2">
                        <label>App Secret</label>
                        <input type="text" class="form-control gateway-secret" value="<?= $gw['app_secret'] ?>">
                    </div>

                    <div class="mb-2">
                        <label>Username</label>
                        <input type="text" class="form-control gateway-user" value="<?= $gw['username'] ?>">
                    </div>

                    <div class="mb-2">
                        <label>Password</label>
                        <input type="text" class="form-control gateway-pass" value="<?= $gw['password'] ?>">
                    </div>

                    <button class="btn btn-success w-100" onclick="saveGateway(this, '<?= $gw_name ?>')">💾
                        Save</button>
                </div>
            </div>



        <?php endforeach; ?>

    </div>



    <!-- Modal -->


    <pre id="jsonOutput" class="mt-4 p-3 bg-dark text-light rounded" hidden></pre>
</div>

<?php include('footer.php'); ?>

<script>
    let gateways = <?= json_encode($admin_data['settings']['payment_gateway'], JSON_PRETTY_PRINT); ?>;

    // Save button clicked for a specific card
    function saveGateway(btn) {
        const card = btn.closest('.card');
        const index = parseInt(card.getAttribute('data-index')); // প্রতিটি কার্ডে data-index attribute থাকবে

        const gateway = {
            gateway: card.querySelector('.gateway-name').value.trim(),
            active: card.querySelector('.gateway-active').value,
            type: card.querySelector('.gateway-type').value.trim(),
            app_key: card.querySelector('.gateway-key').value.trim(),
            app_secret: card.querySelector('.gateway-secret').value.trim(),
            username: card.querySelector('.gateway-user').value.trim(),
            password: card.querySelector('.gateway-pass').value.trim()
        };

        // Update local JS array
        gateways[index] = gateway;

        // Send update to server
        updateGatewayOnServer(index, gateway);
    }

    // Function to send only single gateway update
    function updateGatewayOnServer(index, gateway) {
        const sccode = '<?= $sccode ?>'; // সরাসরি PHP থেকে পাস করা

        fetch('backend/update-gateway.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                sccode: sccode,
                gateways: JSON.stringify({ index: index, data: gateway })
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ Gateway updated successfully');
                    updateOutput();
                } else {
                    alert('❌ ' + data.msg);
                }
            })
            .catch(err => alert("Server errorও: " + err));
    }

    // Update JSON output preview
    function updateOutput() {
        document.getElementById('jsonOutput').textContent = JSON.stringify(gateways, null, 2);
    }

    // Render gateways dynamically
    function renderGateways() {
        const container = document.getElementById('gatewayContainer');
        container.innerHTML = '';
        gateways.forEach((gw, idx) => {
            container.innerHTML += `
        <div class="col-md-12 mb-3">
            <div class="card p-3 shadow-sm" data-index="${idx}">
               
            <div class="row">
            <div class="col-4">
                <span> <img src="assets/images/${gw.gateway}_payment_logo.png"
                        style="height:36px; margin-right:6px;"></span>
                </div>
                <div class="col-8">
                <h5 class="m-0 p-o text-dark float-end">
                    ${gw.gateway.charAt(0).toUpperCase() + gw.gateway.slice(1)} Gateway
                </h5>
                </div>
                
            </div>
            

                
                <hr>

            <div class="row">
                <div class="mb-2"><input type="hidden" class="form-control gateway-name" value="${gw.gateway}"></div>
                <div class="col-3 mb-2"><label>Active</label><select class="form-select gateway-active">
                    <option value="1"${gw.active == 1 ? ' selected' : ''}>Active</option>
                    <option value="0"${gw.active == 0 ? ' selected' : ''}>Inactive</option>
                </select></div>
                <div class="col-md-3 mb-2"><label>Type</label><select  class="form-select gateway-type" >
                <option value="1"${gw.type == 'sandbox' ? ' selected' : ''}>Sandbox</option>
                    <option value="0"${gw.type == 'live' ? ' selected' : ''}>Live</option>
                </select></div>
                <div class="col-md-6 mb-2"><label>App Key</label><input type="text" class="form-control gateway-key" value="${gw.app_key ?? ''}"></div>
                <div class="col-md-6 mb-2"><label>App Secret</label><input type="text" class="form-control gateway-secret" value="${gw.app_secret ?? ''}"></div>
                <div class="col-md-3 mb-2"><label>Username</label><input type="text" class="form-control gateway-user" value="${gw.username ?? ''}"></div>
                <div class="col-md-3 mb-2"><label>Password</label><input type="text" class="form-control gateway-pass" value="${gw.password ?? ''}"></div>
                <button class="col-md-2 mt-3 ms-3 btn btn-success" onclick="saveGateway(this)"> <i class="bi bi-floppy"></i> Save Changes</button>
         </div>
                </div>
        </div>`;
        });
        updateOutput();
    }

    // Initialize
    renderGateways();
</script>

<script>



    // Modal controls
    function showModal() { document.getElementById('gatewayModal').style.display = 'block'; }
    function closeModal() { document.getElementById('gatewayModal').style.display = 'none'; }

    // Add new gateway
    function addGateway() {
        const gw = {
            gateway: document.getElementById('new_gateway').value.trim(),
            active: document.getElementById('new_active').value,
            type: 'sandbox',
            app_key: document.getElementById('new_app_key').value.trim(),
            app_secret: document.getElementById('new_app_secret').value.trim(),
            username: document.getElementById('new_username').value.trim(),
            password: document.getElementById('new_password').value.trim()
        };
        if (!gw.gateway) return alert('⚠️ Gateway name is required!');
        gateways.push(gw);
        closeModal();
        renderGateways();
        updateOutput();
    }



    // Save all gateways to backend
    function saveToServer() {
        const sccode = '<?= $sccode ?? ''; ?>'; // প্রাপ্ত স্কুল কোড
        alert(sccode);

        fetch('backend/update_gateway.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                gateways: JSON.stringify(gateways),
                sccode: sccode
            })
        })
            .then(res => res.json())
            .then(data => {
                alert(data.msg);
                console.log(data);
            })
            .catch(err => alert("Server error--: " + err));
    }

    // Output JSON view
    function updateOutput() {
        document.getElementById('jsonOutput').textContent = JSON.stringify(gateways, null, 2);
    }
    updateOutput();
</script>