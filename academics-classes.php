<?php require_once 'header.php'; ?>

<style>
    /* Drag handle cursor */
    .card-header {
        cursor: grab;
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
        background: #f8f9fa;
        border: 2px dashed #adb5bd;
        border-radius: .5rem;
        min-height: 70px;
    }

    /* Prevent column break jump */
    .class-card {
        position: relative;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- FILTER -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Slot Filter</h6>
                    <div id="slotBox" class="d-flex flex-wrap gap-3"></div>
                </div>
                <div class="col-md-6">
                    <h6>Session Filter</h6>
                    <div id="sessionBox" class="d-flex flex-wrap gap-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div id="classContainer"></div>

</div>

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="areaModal">
    <div class="modal-dialog">
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

                <div class="mb-2">
                    <label>Class</label>
                    <input type="text" name="areaname" class="form-control form-control-sm" required>
                </div>

                <div class="mb-2">
                    <label>Section</label>
                    <input type="text" name="subarea" class="form-control form-control-sm">
                    <small class="text-muted">Class add হলে optional</small>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary btn-sm">Save</button>
            </div>

        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- jQuery UI CSS -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

<!-- jQuery UI JS -->
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

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
        let html = `<label><input type="checkbox" class="${type}All"> All</label>`;
        let all = true;

        list.forEach(v => {
            let chk = saved.length === 0 || saved.includes(v);
            if (!chk) all = false;
            html += `<label><input type="checkbox" class="${type}Chk" value="${v}" ${chk ? 'checked' : ''}> ${v}</label>`;
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

        $.post('academics/get-classes.php', { slots, sessions }, res => {

            let html = '';

            /* =========================
               CASE–1 : কোন ডাটা নাই
               ========================= */
            if (!res || res.length === 0) {

                slots.forEach(slot => {
                    alert(slots + sessions);
                    html += `<div class="mb-4"><h4>${slot}</h4>`;

                    sessions.forEach(session => {
                        html += `
                <div class="ms-3 mb-3">
                    <h6 class="d-flex justify-content-between">
                        ${session}
                        <button class="btn btn-sm btn-primary addClass"
                            data-slot="${slot}" data-session="${session}">
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
                html += `<div class="mb-4"><h4>${slot}</h4>`;

                Object.keys(g[slot]).forEach(session => {
                    html += `
            <div class="ms-3 mb-3">
                <h6 class="d-flex justify-content-between">
                    ${session}
                    <button class="btn btn-sm btn-primary addClass"
                        data-slot="${slot}" data-session="${session}">
                        + Class
                    </button>
                </h6>
                <div class="session-row">`;

                    g[slot][session].forEach(cls => {
                        html += `
                <div class="class-card mb-3"
                    data-class="${cls}" data-slot="${slot}" data-session="${session}">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between">
                            <strong>${cls}</strong>
                            <div>
                                <button class="btn btn-sm btn-outline-primary addSection"
                                    data-class="${cls}" data-slot="${slot}" data-session="${session}">+</button>
                                <button class="btn btn-sm btn-outline-danger delClass"
                                    data-class="${cls}" data-slot="${slot}" data-session="${session}">×</button>
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

        }, 'json');

    }


    function enableClassDrag() {

        $('.session-row').sortable({
            items: '.class-card',
            handle: '.card-header',
            placeholder: 'sortable-placeholder',
            tolerance: 'pointer',

            update: function () {

                let order = [];

                $(this).children('.class-card').each(function (i) {
                    order.push({
                        classname: $(this).data('class'),
                        slot: $(this).data('slot'),
                        session: $(this).data('session'),
                        position: i + 1
                    });
                });

                console.log(order); // 🔴 প্রথমে এটা দেখো

                $.ajax({
                    url: 'academics/update-class-order.php',
                    type: 'POST',
                    data: { order: JSON.stringify(order) },
                    success: function (res) {
                        showToast("success", "Re-arrange Classes Updated!");
                        console.log(res);
                    }
                });
            }
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
                            <strong>${r.subarea}</strong><br>
                            <small>👨‍🏫 ${r.teacher_name ?? '-'} | 👥 ${r.student_count}</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary editArea">✎</button>
                            <button class="btn btn-sm btn-outline-danger delArea">🗑</button>
                        </div>
                    </div>
                </div>`;
                });

                sectionList.html(html);

                enableSectionDrag(sectionList, card);

            }, 'json');
        });
    }


    function enableSectionDrag(sectionList, card) {

        sectionList.sortable({
            items: '.section-item',
            placeholder: 'sortable-placeholder',
            tolerance: 'pointer',

            update: function () {

                let order = [];

                sectionList.children('.section-item').each(function (i) {
                    order.push({
                        section_id: $(this).data('id'),
                        classname: card.data('class'),
                        slot: card.data('slot'),
                        session: card.data('session'),
                        position: i + 1
                    });
                });

                console.log('ORDER' + JSON.stringify(order)); // 🔴 debug

                $.ajax({
                    url: 'academics/update-section-order.php',
                    type: 'POST',
                    data: { order: JSON.stringify(order) },
                    success: function (res) {
                        console.log(res);
                        showToast("success", "Re-arrange Sections Updated!");

                    }
                });
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
        $.post('academics/class-get.php', { id }, r => {
            $('[name=mode]').val('edit');
            $('[name=id]').val(r.id);
            $('[name=areaname]').val(r.areaname);
            $('[name=subarea]').val(r.subarea);
            $('[name=slot]').val(r.slot);
            $('[name=sessionyear]').val(r.sessionyear);
            areaModal.show();
        }, 'json');
    });

    $('#areaForm').submit(function (e) {
        e.preventDefault();
        $.post('academics/save-classes.php', $(this).serialize(), res => {
            if (res.status === 'ok') {
                areaModal.hide();
                loadClasses();
            } else alert(res.msg);
        }, 'json');
    });

    /* ================= DELETE ================= */

    $(document).on('click', '.delArea', function () {
        let id = $(this).closest('.section-item').data('id');
        if (confirm('Delete section?'))
            $.post('academics/class-delete.php', { id }, () => loadClasses(), 'json');
    });

    $(document).on('click', '.delClass', function () {
        if (!confirm('Delete class & sections?')) return;
        $.post('academics/class-delete-all.php', {
            areaname: $(this).data('class'),
            slot: $(this).data('slot'),
            session: $(this).data('session')
        }, () => loadClasses(), 'json');
    });
</script>

</body>

</html>