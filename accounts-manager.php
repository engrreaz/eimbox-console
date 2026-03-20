<?php require_once 'header.php'; ?>
<?php 

// --- Account Head (Add/Update/Delete) ---
if (isset($_POST['save_head'])) {
    $head_name = mysqli_real_escape_string($conn, $_POST['head_name']);
    if (isset($_POST['head_id']) && !empty($_POST['head_id'])) {
        $id = $_POST['head_id'];
        $conn->query("UPDATE account_head SET account_head='$head_name' WHERE id='$id' AND sccode='$sccode'");
    } else {
        $conn->query("INSERT INTO account_head (account_head, sccode) VALUES ('$head_name', '$sccode')");
    }
    // header("Location: accounts-manager.php"); exit();
}

if (isset($_GET['del_head'])) {
    $id = $_GET['del_head'];
    $conn->query("DELETE FROM account_head WHERE id='$id' AND sccode='$sccode'");
    $conn->query("DELETE FROM account_sub_head WHERE account_head_id='$id' AND sccode='$sccode'");
    // header("Location: accounts-manager.php"); exit();
}

// --- Sub Head (Add/Update/Delete) ---
if (isset($_POST['save_sub'])) {
    $sub_name = mysqli_real_escape_string($conn, $_POST['sub_name']);
    $h_id = $_POST['h_id'];
    $h_name = $_POST['h_name'];
    $inc = isset($_POST['income']) ? 1 : 0;
    $exp = isset($_POST['expenditure']) ? 1 : 0;

    if (isset($_POST['sub_id']) && !empty($_POST['sub_id'])) {
        $sid = $_POST['sub_id'];
        $conn->query("UPDATE account_sub_head SET sub_head='$sub_name', income='$inc', expenditure='$exp' WHERE id='$sid' AND sccode='$sccode'");
    } else {
        $conn->query("INSERT INTO account_sub_head (sccode, account_head_id, account_head, sub_head, income, expenditure) 
                      VALUES ('$sccode', '$h_id', '$h_name', '$sub_name', '$inc', '$exp')");
    }
    // header("Location: accounts-manager.php"); exit();
}

if (isset($_GET['del_sub'])) {
    $id = $_GET['del_sub'];
    $conn->query("DELETE FROM account_sub_head WHERE id='$id' AND sccode='$sccode'");
    // header("Location: accounts-manager.php"); exit();
}

/**
 * ২. ডেটা ফেচিং
 */
$heads = $conn->query("SELECT * FROM account_head WHERE sccode='$sccode' ORDER BY id DESC");
$sub_heads_res = $conn->query("SELECT * FROM account_sub_head WHERE sccode='$sccode'");
$sub_heads = [];
while ($row = $sub_heads_res->fetch_assoc()) {
    $sub_heads[$row['account_head_id']][] = $row;
}
?>

<style>
    .account-card { padding: 16px; margin-bottom: 16px; border: 1px solid #f0f0f0; border-radius:8px; }
    .sub-head-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 12px; background: var(--m3-tonal-surface);
        border-radius: 8px; margin-bottom: 6px;
    }
    .badge-m3 { font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; margin-left: 4px; }
    .badge-inc { background: #E8F5E9; color: #2E7D32; }
    .badge-exp { background: #FFEBEE; color: #B3261E; }
    
    .m3-modal-content { border-radius: 8px; padding: 20px; border: none; }
    .m3-fab-add {
        position: fixed; bottom: 85px; right: 20px; width: 56px; height: 56px;
        border-radius: 16px; background: var(--m3-primary-gradient); color: white;
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(103, 80, 164, 0.3); border: none; z-index: 1000;
    }
</style>



<div class="container-xxl flex-grow-1 container-p-y">


        <?php while($h = $heads->fetch_assoc()): ?>
            <div class="m3-card account-card shadow-sm">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="icon-box c-inst" style="width: 40px; height: 40px; font-size: 1.1rem;"><i class="bi bi-folder2-open"></i></div>
                        <div>
                            <div style="font-size: 1rem; font-weight: 900; color: #1C1B1F;"><?php echo $h['account_head']; ?></div>
                            <div style="font-size: 0.7rem; color: #777; font-weight: 700;">HEAD ID: #<?php echo $h['id']; ?></div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="tonal-icon-btn c-info" style="width: 32px; height: 32px; font-size: 0.8rem;" 
                                onclick="editHead('<?php echo $h['id']; ?>', '<?php echo $h['account_head']; ?>')"><i class="bi bi-pencil"></i></button>
                        <button class="tonal-icon-btn c-exit" style="width: 32px; height: 32px; font-size: 0.8rem;" 
                                onclick="deleteItem('accounts-manager.php?del_head=<?php echo $h['id']; ?>')"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>

                <div style="border-top: 1px dashed #eee; padding-top: 12px;">
                    <?php 
                    if (isset($sub_heads[$h['id']])): 
                        foreach($sub_heads[$h['id']] as $sh):
                    ?>
                        <div class="sub-head-item">
                            <div style="overflow: hidden;">
                                <div style="font-size: 0.85rem; font-weight: 700; color: #444;" class="text-truncate"><?php echo $sh['sub_head']; ?></div>
                                <div style="display: flex; gap: 4px; margin-top: 2px;">
                                    <?php if($sh['income']) echo '<span class="badge-m3 badge-inc">INCOME</span>'; ?>
                                    <?php if($sh['expenditure']) echo '<span class="badge-m3 badge-exp">EXPENSE</span>'; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-2 ms-2">
                                <i class="bi bi-pencil-square text-primary" style="cursor:pointer;" 
                                   onclick="editSub('<?php echo $sh['id']; ?>', '<?php echo $sh['sub_head']; ?>', '<?php echo $sh['income']; ?>', '<?php echo $sh['expenditure']; ?>', '<?php echo $h['id']; ?>', '<?php echo $h['account_head']; ?>')"></i>
                                <i class="bi bi-x-circle text-danger" style="cursor:pointer;" onclick="deleteItem('accounts-manager.php?del_sub=<?php echo $sh['id']; ?>')"></i>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div style="font-size: 0.75rem; color: #aaa; text-align: center; font-style: italic;">No sub-sectors defined</div>
                    <?php endif; ?>

                    <button class="btn btn-sm w-100 mt-2" style="background: var(--m3-tonal-container); color: var(--m3-primary); border-radius: 8px; font-weight: 800; font-size: 0.7rem;" 
                            onclick="addSub('<?php echo $h['id']; ?>', '<?php echo $h['account_head']; ?>')">
                        <i class="bi bi-plus-lg me-1"></i> ADD SUB SECTOR
                    </button>
                </div>
            </div>
        <?php endwhile; ?>

</div>
    <button class="m3-fab-add shadow-lg" onclick="addHead()"><i class="bi bi-plus-lg"></i></button>



    

<div class="modal fade" id="headModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content m3-modal-content shadow-lg">
            <h5 class="fw-bold mb-4" id="headModalTitle" style="color: var(--m3-primary);">Account Head</h5>
            <form method="post">
                <input type="hidden" name="head_id" id="head_id">
                <div class="m3-floating-group">
                    <i class="bi bi-folder2 m3-field-icon"></i>
                    <input type="text" name="head_name" id="head_name" class="m3-input-floating" placeholder=" " required>
                    <label class="m3-floating-label">HEAD NAME (E.G. ACADEMIC FEE)</label>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light flex-fill py-2" style="border-radius:12px; font-weight:700;" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" name="save_head" class="btn btn-primary flex-fill py-2" style="border-radius:12px; font-weight:700;">SAVE HEAD</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="subModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content m3-modal-content shadow-lg">
            <h5 class="fw-bold mb-1" id="subModalTitle" style="color: var(--m3-primary);">Add Sub-Sector</h5>
            <p id="parentHeadLabel" class="small text-muted mb-4"></p>
            <form method="post">
                <input type="hidden" name="sub_id" id="sub_id">
                <input type="hidden" name="h_id" id="h_id">
                <input type="hidden" name="h_name" id="h_name">
                
                <div class="m3-floating-group">
                    <i class="bi bi-tag m3-field-icon"></i>
                    <input type="text" name="sub_name" id="sub_name" class="m3-input-floating" placeholder=" " required>
                    <label class="m3-floating-label">SUB SECTOR NAME</label>
                </div>

                <div style="background: var(--m3-tonal-surface); padding: 12px; border-radius: 12px;">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="income" id="income" value="1" checked>
                        <label class="form-check-label fw-bold small" for="income">Available for Income</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="expenditure" id="expenditure" value="1">
                        <label class="form-check-label fw-bold small" for="expenditure">Available for Expense</label>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light flex-fill py-2" style="border-radius:12px; font-weight:700;" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" name="save_sub" class="btn btn-primary flex-fill py-2" style="border-radius:12px; font-weight:700;">SAVE SUB-HEAD</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once 'footer.php'; ?>



<script>
    const headModal = new bootstrap.Modal('#headModal');
    const subModal = new bootstrap.Modal('#subModal');

    function addHead() {
        document.getElementById('headModalTitle').innerText = "Create New Head";
        document.getElementById('head_id').value = "";
        document.getElementById('head_name').value = "";
        headModal.show();
    }

    function editHead(id, name) {
        document.getElementById('headModalTitle').innerText = "Edit Account Head";
        document.getElementById('head_id').value = id;
        document.getElementById('head_name').value = name;
        headModal.show();
    }

    function addSub(hid, hname) {
        document.getElementById('subModalTitle').innerText = "Add Sub-Sector";
        document.getElementById('parentHeadLabel').innerText = "Parent: " + hname;
        document.getElementById('sub_id').value = "";
        document.getElementById('h_id').value = hid;
        document.getElementById('h_name').value = hname;
        document.getElementById('sub_name').value = "";
        document.getElementById('income').checked = true;
        document.getElementById('expenditure').checked = false;
        subModal.show();
    }

    function editSub(id, name, inc, exp, hid, hname) {
        document.getElementById('subModalTitle').innerText = "Edit Sub-Sector";
        document.getElementById('parentHeadLabel').innerText = "Parent: " + hname;
        document.getElementById('sub_id').value = id;
        document.getElementById('h_id').value = hid;
        document.getElementById('h_name').value = hname;
        document.getElementById('sub_name').value = name;
        document.getElementById('income').checked = (inc == 1);
        document.getElementById('expenditure').checked = (exp == 1);
        subModal.show();
    }

    function deleteItem(url) {
        Swal.fire({
            title: 'Delete this item?',
            text: "Warning: Related records may be affected.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B3261E',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = url; }
        });
    }
</script>


<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>