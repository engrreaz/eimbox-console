<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? $_GET['slot'] ?? '';
$session = $_COOKIE['chain-session'] ?? $_GET['session'] ?? '';
?>

<style>
    .pointer {
        cursor: pointer;
    }

    .item.dragging {
        opacity: 0.5;
    }

    .class-row,
    .session-row {
        margin-left: 15px;
    }

    .drag-placeholder {
        height: 60px;
        border: 2px dashed #999;
        margin-bottom: 8px;
        border-radius: 6px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between mb-3">
        <h4>Student Payment Setup</h4>
    </div>

    <div class="row align-items-end g-2">




        <?php
        // $chain = 'class'; // -- class (class/section omit), exam (+exam), subject (+subject)
        // include 'components/slot-tree-modal.php';
        

        $chain_param = '-c 10 -t Choose Values -u -r -b View Settings -h class exam';
        include 'components/slot-tree-ui.php';
        ?>

        <div class="col-md-2 col-9">
            <button class="btn btn-primary btn-sm" onclick="openAdd()">Add New Item</button>
        </div>

    </div>

    <!-- ITEM LIST -->
    <div id="itemlist" class="mt-3">
        <?php
        // SQL
        $sqlAmt = "SELECT itemcode, amount 
           FROM financesetupvalue 
           WHERE sccode='$sccode' 
             AND sessionyear LIKE '%$session%' 
             AND slot='$slot'  AND classname='' and sectionname=''
           ORDER BY id";

        $result = $conn->query($sqlAmt);

        $amounts = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $amounts[$row['itemcode']] = $row['amount'];
            }
        }

        $sql = "SELECT * FROM financesetup WHERE sccode='$sccode' AND sessionyear LIKE '%$session%' AND slot='$slot' ORDER BY slno";
        $rs = $conn->query($sql);
        while ($r = $rs->fetch_assoc()):

            $itemcode = $r['itemcode'];
            if (isset($amounts[$itemcode])) {
                $valAmount = $amounts[$itemcode];
            } else {
                $valAmount = 0;
            }
            ?>
            <div class="card mb-2 item" data-id="<?= $r['id']; ?>" draggable="true">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="pointer d-flex flex-grow-1 align-items-center"
                        onclick="toggleItem(<?= $r['id'] ?>, '<?= $itemcode ?>', <?= $r['splitable'] ?? 0 ?>)">
                        <div class="col-auto">
                            <i class="bi bi-grip-vertical me-4"></i>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-chevron-right me-4"></i>
                        </div>
                        <div class="col-4 flex-grow-2">
                            <strong class="text-info"><?= $r['particulareng']; ?></strong><br>
                            <small class="text-muted"><?= $r['particularben']; ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-subtract icon-22px text-seconday me-3"></i>
                            <i class="bi bi-arrow-repeat icon-22px text-seconday me-3"></i>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">৳
                            <?= $valAmount ?></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openEdit(<?= $r['id'] ?>)">Edit</a></li>
                            <li><a class="dropdown-item" href="#" onclick="delItem(<?= $r['id'] ?>)">Delete</a></li>
                            <li><a class="dropdown-item" href="#"
                                    onclick="openAmountModal(<?= $r['id'] ?>, '<?= $r['itemcode'] ?>', <?= $r['splitable'] ?? 0 ?>, '<?= $r['particulareng'] ?> | <?= $r['particularben'] ?>')">Amount
                                    : ৳
                                    <?= $valAmount ?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body" id="itemBody<?= $r['id'] ?>" style="display:none;"></div>
            </div>
        <?php endwhile; ?>
    </div>

    <button class="btn btn-warning mt-3" onclick="saveOrder()">Update Re-order</button>
</div>

<!-- ================= MODALS ================= -->

<!-- Add/Edit Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Students Payable Fee Item</h5><button class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fid" value="0">
                <div class="row mb-2">
                    <div class="col-md-6"><label>Particular (English)</label><input type="text" id="peng"
                            class="form-control form-control-sm"></div>
                    <div class="col-md-6"><label>Particular (Bangla)</label><input type="text" id="pben"
                            class="form-control form-control-sm"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><label>Frequency</label>
                        <select id="mon" class="form-control form-control-sm">
                            <!-- Months -->
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>

                            <!-- Separator -->
                            <option disabled>─────────────────</option>

                            <!-- Interval -->
                            <option value="0">Every Month</option>
                            <option value="22">2 Months Frequency</option>
                            <option value="33">3 Months Frequency</option>
                            <option value="44">January | May | September</option>
                            <option value="442">April | August | November</option>
                            <option value="66">January | July</option>
                            <option value="662">May | November</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Account Head</label>

                        <select id="acc_head" class="form-control form-control-sm">
                            <option value="">-- Select Account --</option>

                            <?php
                            $sccode = $sccode; // already available
                            
                            // GET HEADS
                            $headQuery = mysqli_query($conn, "
            SELECT * FROM account_head 
            WHERE sccode = '$sccode'
            ORDER BY id ASC
        ");

                            while ($head = mysqli_fetch_assoc($headQuery)) {

                                $head_id = $head['id'];

                                echo '<optgroup class="text-muted" label="' . htmlspecialchars($head['account_head']) . '">';

                                // GET SUB HEADS (income = 1 only)
                                $subQuery = mysqli_query($conn, "
                SELECT * FROM account_sub_head
                WHERE sccode = '$sccode'
                AND account_head_id = '$head_id'
                AND income = 1
                ORDER BY id ASC
            ");

                                while ($sub = mysqli_fetch_assoc($subQuery)) {

                                    echo '<option value="' . $sub['id'] . '">'
                                        . htmlspecialchars($sub['sub_head']) .
                                        '</option>';
                                }

                                echo '</optgroup>';
                            }
                            ?>

                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <label class="me-3"><input type="checkbox" class="form-check-input" id="new_only">
                            New Admission Only</label>
                        <label><input type="checkbox" class="form-check-input" id="splitable">
                            Splitable</label>
                    </div>
                </div>
                <div id="itemMsg" class="small mt-2"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary btn-sm px-5"
                    data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-sm px-5" onclick="saveItem()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Amount Modal -->
<div class="modal fade" id="amountModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Amount</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="afid">
                <input type="hidden" id="aitemcode">
                <input type="hidden" id="aclass">
                <input type="hidden" id="asection">
                <input type="hidden" id="splyn">

                <span class="mb-2"><strong id="ainfo"></strong></span> <i class="bi bi-arrow-right"></i>
                <span class="text-success"> <strong id="set-amount-title"></strong>
                </span>
                <div class="mb-2"><label>Amount</label><input type="number" id="aamount" class="form-control"
                        step="0.01"></div>
                <div id="amountMsg" class="small mt-2"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" onclick="saveAmount()">Save</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ================= JS ================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>


<script>
    const itemModal = new bootstrap.Modal('#itemModal');
    const amountModal = new bootstrap.Modal('#amountModal');


    // ---------- Slot/Session ----------
    function applyFilter(slot, session) {
        if (!slot || !session) return;

        // Cookie set
        setCookie('slot', slot);
        setCookie('session', session);

        // URL থেকে query parameters বের করা
        const urlParams = new URLSearchParams(window.location.search);
        const urlSlot = urlParams.get('slot') || '';
        const urlSession = urlParams.get('session') || '';

        // Cookie এর সাথে compare
        if (slot === urlSlot && session === urlSession) {
            // মিলছে, reload প্রয়োজন নেই
            return;
        } else {
            // না মিললে reload
            window.location.href = '?slot=' + slot + '&session=' + session;
        }
    }



    // Slot/Session change event
    $('#slot-main,#session-main').off('change').on('change', function () {
        let slot = $('#slot-main').val();
        let session = $('#session-main').val();
        applyFilter(slot, session);
    });

    // On page load, set from cookie but DON'T trigger change/reload
    document.addEventListener('DOMContentLoaded', function () {
        let slot = getCookie('slot');
        let session = getCookie('session');
        if (slot) $('#slot-main').val(slot);
        if (session) $('#session-main').val(session);
    });

    // ---------- Add/Edit Item ----------
    function openAdd() {
        $('#fid').val(0);
        $('#peng,#pben').val('');
        $('#mon').val(0);
        $('#new_only,#splitable').prop('checked', false);
        $('#itemMsg').html('');
        itemModal.show();
    }

    function openEdit(id) {
        $.post('payments/get-finance-item.php', { id }, function (res) {
            let d = JSON.parse(res);
            $('#fid').val(d.id);
            $('#peng').val(d.particulareng);
            $('#pben').val(d.particularben);
            $('#mon').val(d.month);
            $('#acc_head').val(d.sub_head);
            $('#new_only').prop('checked', d.new_only == 1);
            $('#splitable').prop('checked', d.splitable == 1);
            $('#itemMsg').html('');
            itemModal.show();
        });
    }

    function saveItem() {
        let data = {
            id: $('#fid').val(),
            eng: $('#peng').val(),
            ben: $('#pben').val(),
            mon: $('#mon').val(),
            acc_head: $('#acc_head').val(),
            new_only: $('#new_only').is(':checked') ? 1 : 0,
            splitable: $('#splitable').is(':checked') ? 1 : 0
        };
        $.post('payments/save-finance-item.php', data, function (res) {
            $('#itemMsg').html(res);
            if (res.includes('success')) setTimeout(() => location.reload(), 600);
        });
    }

    function delItem(id) {
        if (!confirm('Delete this item?')) return;
        $.post('payments/delete-finance-item.php', { id }, () => location.reload());
    }

    // ---------- Amount ----------
    function openAmountModal(fid, itemcode, splitable, itemText, cls = '', sec = '') {
        $('#afid').val(fid);
        $('#aitemcode').val(itemcode);
        $('#aclass').val(cls);
        $('#asection').val(sec);
        $('#set-amount-title').text(itemText);

        let text = 'Item #' + fid;
        if (cls) text += ' | ' + cls;
        if (sec) text += ' - ' + sec;
        $('#ainfo').text(text);
        $('#aamount').val('');
        $('#amountMsg').html('');

        $.post('payments/get-amount.php', { fid, itemcode, class: cls, section: sec, spl: splitable }, function (r) {
            if (!r) return;
            // alert(JSON.stringify(r));
            let amt = JSON.parse(r).amount;
            let spl = JSON.parse(r).splitable;
            $('#aamount').val(amt);
            $('#splyn').val(spl);

            $('#amountModal').one('shown.bs.modal', function () {
                $('#aamount').focus().select();
            });
        });

        amountModal.show();
    }

    function saveAmount() {
        let data = {
            fid: $('#afid').val(),
            fitemcode: $('#aitemcode').val(),
            class: $('#aclass').val(),
            section: $('#asection').val(),
            amount: $('#aamount').val(),
            spl: $('#splyn').val(),
            acc_head: $('#acc_head').val()
        };
        // alert(JSON.stringify(data));

        $.post('payments/save-amount.php', data, function (res) {
            $('#amountMsg').html(res);
            if (res.includes('success')) {
                setTimeout(() => {
                    amountModal.hide();
                    let amt = parseFloat(data.amount).toFixed(2);

                    if (data.section) {
                        $('.sec-amount-btn').each(function () {
                            if ($(this).data('class') === data.class && $(this).data('section') === data.section) {
                                $(this).html('Set Amount (৳ ' + amt + ')');
                            }
                        });
                    }
                }, 500);
            }
        });
    }

    // ---------- Toggle & Load Classes ----------
    function toggleItem(id, itemcode, spl) {
        let box = $('#itemBody' + id);
        if (box.is(':visible')) { box.slideUp(150); return; }
        if (box.data('loaded') === 1) { box.slideDown(150); return; }

        box.html('<div class="text-muted">Loading classes...</div>').slideDown(100);
        $.post('payments/load-item-classes.php', { fid: id, itemcode, spl, session: $('#session-main').val() }, function (res) {
            box.html(res);
            box.data('loaded', 1);
        });
    }

    // ---------- Drag & Drop ----------
    const list = document.getElementById('itemlist');
    let dragItem = null;
    let placeholder = document.createElement('div');
    placeholder.className = 'drag-placeholder';

    list.addEventListener('dragstart', e => {
        if (e.target.classList.contains('item')) {
            dragItem = e.target;
            e.target.classList.add('dragging');
        }
    });

    list.addEventListener('dragend', e => {
        if (dragItem) {
            dragItem.classList.remove('dragging');
            dragItem = null;
        }
    });

    list.addEventListener('dragover', e => {
        e.preventDefault();
        const after = getAfterElement(list, e.clientY);
        if (after == null) {
            list.appendChild(placeholder);
        } else {
            list.insertBefore(placeholder, after);
        }
    });

    list.addEventListener('drop', () => {
        if (placeholder.parentNode) {
            list.insertBefore(dragItem, placeholder);
            placeholder.remove();
        }
    });

    function getAfterElement(container, y) {
        const els = [...container.querySelectorAll('.item:not(.dragging)')];
        return els.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) return { offset: offset, element: child };
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function saveOrder() {
        const ids = [...document.querySelectorAll('.item')].map((el, i) => el.dataset.id + '=' + (i + 1));
        $.post('payments/save-item-order.php', { order: ids.join(',') }, function (res) {
            alert(res);
            location.reload();
        });
    }


    function chainBtnFunc() {
        window.location.reload();
    }

    // alert("OK");
</script>


</body>

</html>