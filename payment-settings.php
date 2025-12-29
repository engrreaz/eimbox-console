<?php
require_once 'header.php';

$slot = $_COOKIE['slot'] ?? $_GET['slot'] ?? '';
$session = $_COOKIE['session'] ?? $_GET['session'] ?? '';
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
        <div class="col-md-2">
            <label class="form-label">Slot</label>
            <select id="slot-main" class="form-select form-select-sm">
                <option value="">Select Slot</option>
                <?php
                $q = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' ORDER BY slotname");
                while ($r = $q->fetch_assoc()) {
                    $sel = ($slot == $r['slotname']) ? 'selected' : '';
                    echo "<option value='{$r['slotname']}' $sel>{$r['slotname']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Session</label>
            <select id="session-main" class="form-select form-select-sm">
                <option value="">Select Session</option>
                <?php
                $q = $conn->query("SELECT syear FROM sessionyear WHERE sccode='$sccode' AND active=1 ORDER BY syear DESC");
                while ($r = $q->fetch_assoc()) {
                    $sel = ($session == $r['syear']) ? 'selected' : '';
                    echo "<option value='{$r['syear']}' $sel>{$r['syear']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-1 col-3 " >
            <button class="btn btn-dark btn-sm square-btn" id="openTree">
                <i class="bi bi-stack fs-6"></i>
            </button>
        </div>
        <?php
        $chain = 'class'; // -- class (class/section omit), exam (+exam), subject (+subject)
        include 'components/slot-tree-modal.php';
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

        // অ্যারে বানানো: itemcode => amount
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
                    <div class="pointer" onclick="toggleItem(<?= $r['id'] ?>, '<?= $itemcode ?>')">
                        <i class="bi bi-chevron-right me-2"></i>
                        <strong><?= $r['particulareng']; ?></strong><br>
                        <small class="text-muted"><?= $r['particularben']; ?></small>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">৳
                            <?= $valAmount ?></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openEdit(<?= $r['id'] ?>)">Edit</a></li>
                            <li><a class="dropdown-item" href="#" onclick="delItem(<?= $r['id'] ?>)">Delete</a></li>
                            <li><a class="dropdown-item" href="#"
                                    onclick="openAmountModal(<?= $r['id'] ?>, '<?= $r['itemcode'] ?>')">Amount : ৳
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
                <h5 class="modal-title">Fee Item</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fid" value="0">
                <div class="row mb-2">
                    <div class="col-md-6"><label>Particular (English)</label><input type="text" id="peng"
                            class="form-control"></div>
                    <div class="col-md-6"><label>Particular (Bangla)</label><input type="text" id="pben"
                            class="form-control"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><label>Frequency</label>
                        <select id="mon" class="form-control">
                            <option value="0">Every Month</option>
                            <option value="22">2 Months</option>
                            <option value="33">3 Months</option>
                            <option value="44">4 Months</option>
                            <option value="66">6 Months</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <label class="me-3"><input type="checkbox" id="new_only"> New Admission Only</label>
                        <label><input type="checkbox" id="splitable"> Splitable</label>
                    </div>
                </div>
                <div id="itemMsg" class="small mt-2"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" onclick="saveItem()">Save</button>
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
                <div class="mb-2"><strong id="ainfo"></strong></div>
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

    // ---------- Add/Edit ----------
    function openAdd() {
        $('#fid').val(0); $('#peng,#pben').val(''); $('#mon').val(0);
        $('#new_only,#splitable').prop('checked', false); $('#itemMsg').html('');
        itemModal.show();
    }

    function openEdit(id) {
        $.post('payments/get-finance-item.php', { id }, function (res) {
            let d = JSON.parse(res);
            $('#fid').val(d.id); $('#peng').val(d.particulareng); $('#pben').val(d.particularben);
            $('#mon').val(d.mon); $('#new_only').prop('checked', d.new_only == 1); $('#splitable').prop('checked', d.splitable == 1);
            $('#itemMsg').html('');
            itemModal.show();
        });
    }

    function saveItem() {
        let data = {
            id: $('#fid').val(), eng: $('#peng').val(), ben: $('#pben').val(), mon: $('#mon').val(),
            new_only: $('#new_only').is(':checked') ? 1 : 0, splitable: $('#splitable').is(':checked') ? 1 : 0
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

    // ---------- Toggle & Load Classes ----------
    function toggleItem(id, itemcode) {
        let box = $('#itemBody' + id);
        if (box.is(':visible')) { box.slideUp(150); return; }
        if (box.data('loaded') === 1) { box.slideDown(150); return; }

        box.html('<div class="text-muted">Loading classes...</div>').slideDown(100);
        $.post('payments/load-item-classes.php', { fid: id, itemcode: itemcode, session: $('#session-main').val() }, function (res) {
            box.html(res); box.data('loaded', 1);
            // box.find('.pointer').click(function () { $(this).siblings('div').slideToggle(150); $(this).find('i').toggleClass('bi-chevron-right bi-chevron-down'); });
        });
    }

    // class expand
    $('.class-toggle').off('click').on('click', function () {
        $(this).find('i').toggleClass('bi-chevron-right bi-chevron-down');
        $(this).closest('.class-row').find('.section-wrap').first().slideToggle(150);
    });

    // class expand
    $('.section-toggle').off('click').on('click', function () {
        $(this).find('i').toggleClass('bi-chevron-right bi-chevron-down');
        $(this).closest('.class-row').find('.section-wrap').first().slideToggle(150);
    });

    // ---------- Amount ----------
    function openAmountModal(fid, itemcode, cls = '', sec = '') {
        $('#afid').val(fid); $('#aitemcode').val(itemcode); $('#aclass').val(cls); $('#asection').val(sec);
        let text = 'Item #' + fid; if (cls) text += ' | ' + cls; if (sec) text += ' - ' + sec;
        $('#ainfo').text(text); $('#aamount').val(''); $('#amountMsg').html('');

        $.post('payments/get-amount.php', { fid: fid, itemcode: itemcode, class: cls, section: sec }, function (r) {
            if (r) $('#aamount').val(JSON.parse(r).amount);
        });

        amountModal.show();
    }

    function saveAmount() {
        let data = { fid: $('#afid').val(), fitemcode: $('#aitemcode').val(), class: $('#aclass').val(), section: $('#asection').val(), amount: $('#aamount').val() };

        $.post('payments/save-amount.php', data, function (res) {
            $('#amountMsg').html(res);
            if (res.includes('success')) {
                setTimeout(() => {
                    amountModal.hide();
                    let amt = parseFloat(data.amount).toFixed(2);
                    let cls = data.class, sec = data.section;
                    if (cls && sec) {
                        let btn = $('.session-row').filter(function () { return $(this).find('span').text() === sec; }).find('button');
                        btn.html('Set Amount (৳ ' + parseFloat(data.amount).toFixed(2) + ')');
                    }

                    if (data.section) {
                        $('.sec-amount-btn').each(function () {
                            if (
                                $(this).data('class') === data.class &&
                                $(this).data('section') === data.section
                            ) {
                                $(this).html('Set Amount (৳ ' + amt + ')');
                            }
                        });
                    }
                }, 500);
            }
        });
    }

    // ---------- Drag & Drop ----------
    const list = document.getElementById('itemlist'); let dragItem = null;
    list.addEventListener('dragstart', e => { if (e.target.classList.contains('item')) { dragItem = e.target; e.target.classList.add('dragging'); } });
    list.addEventListener('dragend', e => { if (dragItem) { dragItem.classList.remove('dragging'); dragItem = null; } });
    list.addEventListener('dragover', e => { e.preventDefault(); const after = getAfterElement(list, e.clientY); if (after == null) { list.appendChild(dragItem); } else { list.insertBefore(dragItem, after); } });
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
        $.post('payments/save-item-order.php', { order: ids.join(',') }, function (res) { alert(res); location.reload(); });
    }

    // ---------- Slot/Session ----------
    function applyFilter() {
        let slot = $('#slot-main').val(), session = $('#session-main').val();
        if (!slot || !session) { alert('Please select slot and session'); return; }
        setCookie('slot', slot); setCookie('session', session);
        window.location.href = '?slot=' + slot + '&session=' + session;
    }
    $('#slot-main,#session-main').on('change', () => applyFilter());
    function setCookie(name, value) { document.cookie = name + '=' + value + ';path=/'; }
    function getCookie(name) { let v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)'); return v ? v[2] : null; }
    document.addEventListener('DOMContentLoaded', function () { let slot = getCookie('slot'), session = getCookie('session'); if (slot) $('#slot-main').val(slot); if (session) $('#session-main').val(session); });

</script>


<script>
    let placeholder = document.createElement('div');
    placeholder.className = 'drag-placeholder';

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

</script>
</body>

</html>


১. আইটেম ব্লকের অ্যামাউন্ট সেট করা ঠিক আছে।
২. ক্লাস ব্লকে অ্যামাউন্ট সেট করার বাটন/সুযোগ নাই। + অ্যামাউন্ট দেখারও ব্যবস্থা নাই।
৩. ক্লাস ব্লক এক্সপান্ড করলে সেকশন ব্লকগুলো এক্সপান্ড হয়ে কলাপ্স হয়ে যায়। ওখানে অ্যামাউন্ট সেট করা যায়। ডিসপ্লে হয় না।
৪. মূল কার্ড ড্রাগ ড্রপ হচ্ছে, ড্রাগ ড্রপের সময় একটা ডটেড এরিয়া দেকালে মনে হয় ভালো হয়।
ধাপে ধাপে কি কি পরিবর্তন করবো, কোড সহ দাও