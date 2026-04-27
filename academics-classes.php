<?php require_once 'header.php'; ?>

<?php 
// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';
?>

<style>
    /* Drag handle cursor */

    .card-header {
        cursor: move;
        pointer-events: auto !important;
    }

    .card-header:active {
        cursor: grabbing;
    }

    /* While dragging */
    .ui-sortable-helper {
        opacity: 0.85;
        transform: scale(1.02);
        box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
    }

    /* Smooth movement animation */
    .session-row>.class-card {
        transition: transform .2s ease;
    }

    /* Drop placeholder */
    .sortable-placeholder {
        height: 60px;
        background: #f5f5f5;
        border: 2px dashed #999;
        margin-bottom: 10px;
    }

    /* Prevent column break jump */
    .class-card {
        position: relative;
    }

    .sortable-ghost {
        opacity: 0.4;
    }

    .sortable-chosen {
        background: #e3f2fd;
        border: 2px dashed #2196f3;
    }

    .sortable-drag {
        background: #ffffff;
        border: 2px solid #0d6efd;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
    }

    /* Placeholder space */
    .sortable-placeholder {
        height: 60px;
        border: 2px dashed #999;
        background: #f8f9fa;
        margin-bottom: 10px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- FILTER --> 
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Slot Filter</h6>
                    <div id="slotBox" class="d-flex flex-wrap gap-3 tour" data-feature="Slot"></div>
                </div>
                <div class="col-md-6">
                    <h6>Session Filter</h6>
                    <div id="sessionBox" class="d-flex flex-wrap gap-3 tour" data-feature="Session"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div id="classContainer"></div>

</div>

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="areaModal">
    <div class="modal-dialog modal-dialog-centered ">
        <form id="areaForm" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Class / Section</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="mode" value="add">
                <input type="hidden" name="id">
                <input type="hidden" name="slot">
                <input type="hidden" name="sessionyear">
                <?php
                $classesSetting = null;
                foreach ($sett as $item) {
                    if ($item['setting_title'] === 'Classes') {
                        $classesSetting = $item;
                        break;
                    }
                }

                if ($classesSetting) {
                    $classesArray = explode(',', $classesSetting['settings_value']);
                } else {
                    echo "Setting 'Classes' not found.";
                }
                ?>

                <div class="mb-2">
                    <label>Class</label>
                    <select name="areaname" class="form-control form-control-sm" required>
                        <option value="">Select a class</option>
                        <?php
                        foreach ($classesArray as $class) {
                            echo '<option value="' . htmlspecialchars($class) . '">' . htmlspecialchars($class) . '</option>';
                        }

                        ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label>Section</label>
                    <input type="text" name="subarea" class="form-control form-control-sm">
                </div>
                <div class="mb-2">
                    <label>Class Teacher</label>
                    <?php
                    $query = "SELECT tid, tname FROM teacher WHERE sccode = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $sccode);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $teachers = $result->fetch_all(MYSQLI_ASSOC);
                    ?>

                    <select name="teacher" class="form-control form-control-sm">
                        <option value="">Select a teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= htmlspecialchars($teacher['tid']) ?>">
                                <?= htmlspecialchars($teacher['tid']) ?> | <?= htmlspecialchars($teacher['tname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php
                    // Close statement and result
                    $stmt->close();
                    ?>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary btn-sm" data-feature="Add/Edit Class/Section" data-points="5">Save</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>






<script>
    let areaModal = new bootstrap.Modal('#areaModal');

    $(function () {
        loadSlots();
        loadSessions();

    });

    /* ================= FILTER ================= */

    function loadSlots() {
        $.getJSON('academics/get-slots.php', res => {
            renderCheck('#slotBox', 'slot', res, 'area_slots');
        });
    }

    function loadSessions() {
        $.getJSON('academics/get-sessions.php', res => {
            renderCheck('#sessionBox', 'session', res, 'area_sessions');
        });
    }

    function renderCheck(box, type, list, store) {
        let saved = JSON.parse(localStorage.getItem(store)) || [];
        let html = `<label class="form-check form-check-danger "><input type="checkbox" class="${type}All form-check-input form-check-xl   "> All</label>`;
        let all = true;

        list.forEach(v => {
            let chk = saved.length === 0 || saved.includes(v);
            if (!chk) all = false;
            html +=
                `<label class="form-check form-check-info "><input type="checkbox" class="${type}Chk  form-check-input form-check-xl  " value="${v}" ${chk ? 'checked' : ''}> ${v}</label>`;
        });

        $(box).html(html);
        $(`.${type}All`).prop('checked', all);
        loadClasses();
    }

    $(document).on('change', '.slotChk,.slotAll,.sessionChk,.sessionAll', function () {
        if ($(this).hasClass('slotAll')) $('.slotChk').prop('checked', this.checked);
        if ($(this).hasClass('sessionAll')) $('.sessionChk').prop('checked', this.checked);
        saveState();
        loadClasses();
    });

    function saveState() {
        localStorage.setItem('area_slots', JSON.stringify($('.slotChk:checked').map((i, e) => e.value).get()));
        localStorage.setItem('area_sessions', JSON.stringify($('.sessionChk:checked').map((i, e) => e.value).get()));
    }

    /* ================= LOAD CLASSES ================= */

    function loadClasses() {
        let slots = $('.slotChk:checked').map((i, e) => e.value).get();
        let sessions = $('.sessionChk:checked').map((i, e) => e.value).get();


        if (!slots.length || !sessions.length) {
            $('#classContainer').html('<div class="text-muted">No data</div>');
            return;
        }

        $.post('academics/get-classes.php', {
            slots,
            sessions
        }, res => {

            let html = '';

            /* =========================
               CASE–1 : কোন ডাটা নাই
               ========================= */
            if (!res || res.length === 0) {

                slots.forEach(slot => {
                    // alert(slots + sessions);
                    html += `<div class="mb-4"><h4 class="fw-bold text-primary text-center  ">${slot}</h4>`;

                    sessions.forEach(session => {
                        html += `
                        <div class="ms-3 mb-3">
                            <h6 class="d-flex justify-content-between">
                            Session :  ${session}
                                <button class="btn btn-sm btn-primary addClass tour" id="addClass_${slot}_${session}"
                                    data-slot="${slot}" data-session="${session}"  data-perm="2">
                                    + Class
                                </button>
                            </h6>
                            <div class="session-row text-muted small">
                                No class added yet
                            </div>
                        </div>`;
                    });

                    html += `</div>`;
                });

                $('#classContainer').html(html);
                return;
            }

            /* =========================
               CASE–2 : ডাটা আছে
               ========================= */
            let g = {};

            res.forEach(r => {
                if (!g[r.slot]) g[r.slot] = {};
                if (!g[r.slot][r.sessionyear]) g[r.slot][r.sessionyear] = [];
                g[r.slot][r.sessionyear].push(r.areaname);
            });

            Object.keys(g).forEach(slot => {
                html += `<div class="mb-4">
                
                   <div class="divider divider-primary m-0 p-0 mx-4"
                            style="--bs-divider-color:teal;">
                            <div class="divider-text fs-5 fw-bold "
                                style="color: teal"> ${slot}
                            </div>
                        </div>
                
                
                
                
                `;

                Object.keys(g[slot]).forEach(session => {
                    html += `
            <div class="ms-3 mb-3">
                <h6 class="d-flex justify-content-between">
                    Session : ${session} 
                    <button class="btn btn-sm btn-primary addClass tour" id="addClass_${slot}_${session}"
                        data-slot="${slot}" data-session="${session}"  data-perm="2">
                        + Class
                    </button>
                </h6>
                <div class="session-row">`;

                    g[slot][session].forEach(cls => {
                        html += `
                <div class="class-card mb-3"
                    data-class="${cls}" data-slot="${slot}" data-session="${session}">
                    <div class="card ">
                        <div class="card-header d-flex justify-content-between">
                        <i class="bi bi-grip-horizontal pt-2" style="cursor: grabbing;"></i> 
                            <strong class="flex-grow-1 ms-3 fs-5 pt-1">${cls}</strong> 
                            <div>
                                <button class="btn btn-sm btn-outline-primary addSection tour" id="addSection_${slot}_${session}_${cls}"
                                    data-class="${cls}" data-slot="${slot}" data-session="${session}" data-perm="2"><i class="bi bi-plus fs-6 p-0"></i></button>
                                <button class="btn btn-sm btn-outline-danger delClass ms-1 tour" id="delClass_${slot}_${session}_${cls}"
                                    data-class="${cls}" data-slot="${slot}" data-session="${session}" data-perm="3" data-feature="Delete Class" data-points="-3"><i class="bi bi-x fs-6"></i></button>
                            </div>
                        </div>
                        <div class="card-body section-list"></div>
                    </div>
                </div>`;
                    });

                    html += `</div></div>`;
                });

                html += `</div>`;
            });

            $('#classContainer').html(html);
            loadSections();
            enableClassDrag();
            setTimeout(applyPermission, 200);
             populateElementDropdown("element_id");
        }, 'json');

    }


    function enableClassDrag() {

        document.querySelectorAll('.session-row').forEach(el => {

            new Sortable(el, {
                animation: 150,
                handle: '.card-header',

                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',

                onEnd: function () {
                    let order = [];

                    el.querySelectorAll('.class-card').forEach((item, i) => {
                        order.push({
                            classname: item.dataset.class,
                            slot: item.dataset.slot,
                            session: item.dataset.session,
                            position: i + 1
                        });
                    });

                    $.post('academics/update-class-order.php', {
                        order: JSON.stringify(order)
                    }, () => {
                        showToast("success", "Re-arrange Classes Updated!");
                    });
                }
            });

        });
    }









    /* ================= SECTIONS ================= */

    function loadSections() {

        $('.class-card').each(function () {

            let card = $(this);
            let sectionList = card.find('.section-list');

            $.post('academics/get-sections.php', {
                class: card.data('class'),
                slot: card.data('slot'),
                session: card.data('session')
            }, function (res) {

                let html = '';

                res.forEach(r => {
                    html += `
                <div class="border p-2 mb-2 section-item"
                     data-id="${r.id}">
                    <div class="d-flex justify-content-between">
                    <div>
                        <i class="bi bi-grip-vertical" style="cursor: grabbing;"></i>
                    </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>${r.subarea}</strong><br>
                            <small>
                            <img src="${r.photourl}" style="height:24px; width:24px; border-radius:50%; object-fit:cover; margin-right:10px; margin-top:6px;" />
                        <span style="padding-top:10px;">
                            ${r.classteacher ?? '-'} | 👥 ${r.student_count}
                        </span>    
                        
                            
                            
                            </small>
                        </div>
                        <div class="pt-3">
                            <button class="btn btn-sm btn-outline-primary editArea py-2 px-2 tour" id="editArea_${r.id}" data-perm="3">
                            <i class="bi bi-pencil-square fs-5"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delArea py-2 px-2 ms-1 tour" id="delArea_${r.id}" data-perm="3" data-feature="Delete Section" data-points="-1"><i class="bi bi-trash fs-5"></i></button>
                        </div>
                    </div>
                </div>`;
                });

                sectionList.html(html);

                enableSectionDrag(sectionList, card);
                setTimeout(applyPermission, 200);
                populateElementDropdown("element_id");
            }, 'json');
        });

    }


    function enableSectionDrag(sectionList, card) {

        new Sortable(sectionList[0], {
            animation: 150,
            handle: '.bi-grip-vertical',

            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',

            onEnd: function () {

                let order = [];

                sectionList.find('.section-item').each(function (i) {
                    order.push({
                        section_id: $(this).data('id'),
                        position: i + 1,
                        class: card.data('class'),
                        slot: card.data('slot'),
                        session: card.data('session')
                    });
                });

                $.post('academics/update-section-order.php', {
                    order: JSON.stringify(order)
                }, function (res) {

                    if (res.status === 'ok') {
                        showToast("success", "Section order updated!");
                    } else {
                        showToast("error", "Failed to update order");
                    }

                }, 'json');
            }
        });
    }

    /* ================= ADD / EDIT ================= */

    $(document).on('click', '.addClass,.addSection', function () {
        $('#areaForm')[0].reset();
        $('[name=mode]').val('add');
        $('[name=slot]').val($(this).data('slot'));
        $('[name=sessionyear]').val($(this).data('session'));
        if ($(this).hasClass('addSection'))
            $('[name=areaname]').val($(this).data('class'));
        areaModal.show();
    });

    $(document).on('click', '.editArea', function () {
        let id = $(this).closest('.section-item').data('id');
        $.post('academics/class-get.php', {
            id
        }, r => {
            $('[name=mode]').val('edit');
            $('[name=id]').val(r.id);
            $('[name=areaname]').val(r.areaname);
            $('[name=subarea]').val(r.subarea);
            $('[name=slot]').val(r.slot);
            $('[name=sessionyear]').val(r.sessionyear);
            $('[name=teacher]').val(r.classteacher);
            areaModal.show();
        }, 'json');
    });

    $('#areaForm').submit(function (e) {
        e.preventDefault();

        // check if modal is visible
        if (!$('#areaModal').hasClass('show')) {
            console.log('Modal closed, submission cancelled');
            return;
        }

        let formData = $(this).serializeArray();
        let isEdit = $('[name=mode]').val() === 'edit';
        let isSection = $('[name=subarea]').val().trim() !== '';

        if (isEdit && !isSection) {
            alert('Please provide a section name for editing');
            return;
        }

        if (!isSection && !confirm('Are you sure you want to add a class without a section?')) {
            return;
        }

        // disable submit button to prevent double click
        let $btn = $(this).find('button[type=submit]');
        $btn.prop('disabled', true);

        $.ajax({
            url: 'academics/save-classes.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false); // re-enable

                if (res.status === 'ok') {
                    areaModal.hide();
                    $('#areaForm')[0].reset(); // reset form
                    loadClasses();
                    showToast("success", isEdit ? "Section updated successfully!" : "Class added successfully!");
                } else {
                    showToast("error", res.msg || "An error occurred while saving.");
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false);
                showToast("error", "An error occurred while saving (XHR) : " + error);
            }
        });
    });


    /* ================= DELETE ================= */

    // Delete Section
    $(document).on('click', '.delArea', function () {

        let id = $(this).closest('.section-item').data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Delete section?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.post('academics/class-delete.php', { id }, function (res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Section deleted successfully',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    loadClasses();

                }, 'json');

            }

        });

    });


    // Delete Class + Sections
    $(document).on('click', '.delClass', function () {

        let data = {
            areaname: $(this).data('class'),
            slot: $(this).data('slot'),
            session: $(this).data('session')
        };

        Swal.fire({
            title: 'Are you sure?',
            text: 'Delete class & all sections?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.post('academics/class-delete-all.php', data, function (res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Class & sections deleted',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    loadClasses();

                }, 'json');

            }

        });

    });
</script>
 
<script>
    setTimeout(applyPermission, 200);
</script>

</body>

</html>