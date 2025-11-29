<?php require_once 'header.php'; ?>



<!-- {"module":["Attendance","Orion","Payment","Result","Seed","Student"],"active_module":["Attendance","Seed","Student"],"settings":{"theme":"dark","sms":{"api":{"api_key":"sf;sfskldfjs","api_secret":"123456000"},"in_time":{"active":"1","time":"11:00"},"gateway":{"sms_api":1,"api_key":"sfsdfs","secret_key":"teertre","username":"aaa","password":"bbb","uri":"sfsfdsfsdffsfs"}}}} -->

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    // Fetch scinfo data
    $sql = "SELECT * FROM scinfo WHERE sccode='$sccode' LIMIT 1";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows == 0) {
        echo "<div class='alert alert-danger'>School info not found!</div>";
        include 'footer.php';
        exit;
    }

    $scinfo = $res->fetch_assoc();

    // List of gateways
    $gateway_list = ['bkash', 'nagad', 'rocket', 'bank'];
    $gateways = [];

    // Prepare parsed data
    foreach ($gateway_list as $gw) {
        $raw = trim($scinfo[$gw] ?? "");
        $p = explode(" | ", $raw);

        $gateways[$gw] = [
            "gateway" => $p[0] ?? $gw,
            "active" => $p[1] ?? 0,
            "type" => $p[2] ?? "sandbox",
            "app_key" => $p[3] ?? "",
            "app_secret" => $p[4] ?? "",
            "username" => $p[5] ?? "",
            "password" => $p[6] ?? "",
        ];
    }
    ?>

    <div class="row">

        <?php foreach ($gateway_list as $index => $gw_name):
            $gw = $gateways[$gw_name];
            ?>
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm p-3" data-index="<?= $index ?>" data-gateway="<?= $gw_name ?>">

                    <h5 class="text-primary mb-3">
                        <img class="pe-4" src="assets/images/<?= $gw_name ?>_payment_logo.png" height="24" style="filter: invert(100%);">
                        <?= ucfirst($gw_name) ?> Gateway
                    </h5>

                    <input type="hidden" class="gw-name" value="<?= $gw['gateway'] ?>">
                    <div class="row">



                        <div class="col-md-6">
                            <label class="form-label ps-2">Active</label>
                            <select class="form-select mb-2 gw-active form-select-sm">
                                <option value="1" <?= ($gw['active'] == 1 ? 'selected' : '') ?>>Active</option>
                                <option value="0" <?= ($gw['active'] == 0 ? 'selected' : '') ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ps-2">Type</label>
                            <select class="form-select mb-2 gw-type form-select-sm">
                                <option value="sandbox" <?= ($gw['type'] == 'sandbox' ? 'selected' : '') ?>>Sandbox</option>
                                <option value="live" <?= ($gw['type'] == 'live' ? 'selected' : '') ?>>Live</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label ps-2">App Key</label>
                            <input type="text" class="form-control mb-2 gw-key form-control-sm"
                                value="<?= $gw['app_key'] ?>">

                        </div>
                        <div class="col-md-6">
                            <label class="form-label ps-2">App Secret</label>
                            <input type="text" class="form-control mb-2 gw-secret form-control-sm"
                                value="<?= $gw['app_secret'] ?>">

                        </div>
                        <div class="col-md-6">
                            <label class="form-label ps-2">Username</label>
                            <input type="text" class="form-control mb-2 gw-user form-control-sm "
                                value="<?= $gw['username'] ?>">

                        </div>
                        <div class="col-md-6">
                            <label class="form-label ps-2">Password</label>
                            <input type="text" class="form-control mb-3 gw-pass form-control-sm"
                                value="<?= $gw['password'] ?>">

                        </div>
                        <div class="col-md-3">

                            <button class=" btn btn-success w-100" onclick="saveGateway(this)" >
                                <i class="bi bi-floppy pe-3"></i> Save


                            </button>

                        </div>





                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

</div>

<?php require_once 'footer.php'; ?>

<script>
    function saveGateway(btn) {
        const card = btn.closest(".card");

        const gatewayName = card.getAttribute("data-gateway");

        const data = {
            gateway: gatewayName,
            active: card.querySelector(".gw-active").value,
            type: card.querySelector(".gw-type").value,
            app_key: card.querySelector(".gw-key").value,
            app_secret: card.querySelector(".gw-secret").value,
            username: card.querySelector(".gw-user").value,
            password: card.querySelector(".gw-pass").value
        };

        fetch("backend/update-gateway.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                sccode: "<?= $sccode ?>",
                gateway_name: gatewayName,
                data: JSON.stringify(data)
            })
        })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    showToast("success", "✔ Saved successfully", "Success");
                    // showNotification("Success", "Gateway settings saved successfully.", "success");
                    // alert("✔ Saved successfully");
                } else {
                    showToast("error", "❌ Save Failed" + res.msg, "Error");

                    // alert("❌ " + res.msg);
                }
            })
            .catch(() => alert("Server error occurred"));
    }
</script>


</body>

</html>