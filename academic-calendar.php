<?php require_once 'header.php'; ?>

<style>
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 0, 0, .7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
        }
    }

    .pulse-now {
        animation: pulse 1.5s infinite;
    }

    .modal {
        z-index: 30000 !important;
    }

    .modal-backdrop {
        z-index: 29999 !important;
    }
</style>



<div class="container-xxl flex-grow-1 container-p-y">

    <div class="dropdown float-end mb-2">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="moreMenuBtn"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots"></i>
        </button>
        <ul class="dropdown-menu" aria-labelledby="moreMenuBtn">
            <li><a class="dropdown-item" href="#" id="printCalendar">Print</a></li>
            <li><a class="dropdown-item" href="#" id="filterEvents">Filter</a></li>
            <li><a class="dropdown-item" href="#" id="generalEntry">General Entry</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#" id="importEvents">Import</a></li>
        </ul>
    </div>



    <div class="row mb-3">
        <div class="col-md-4 h-100">
            <div class="h-100" id="nextEventBox"></div>
        </div>
        <div class="col-md-4 h-100">
            <div class="h-100" id="todayTimeline"></div>
        </div>
        <div class="col-md-4 h-100">
            <div class="h-100" id="curEvents"></div>
        </div>
    </div>


    <div id="calendar"></div>







</div>



<!-- Import Modal -->
<div class="modal fade" id="modalOfImport" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import General Events</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2">Filter by Type:</label>
                    <select id="importTypeFilter" class="form-control form-control-sm w-auto me-3">
                        <option value="all">All</option>
                        <option value="holiday">Holiday</option>
                        <option value="exam">Exam</option>
                        <option value="class">Class</option>
                        <option value="sports">Sports</option>
                        <option value="meeting">Meeting</option>
                        <option value="notice">Notice</option>
                        <option value="other">Other</option>
                    </select>
                    <button id="importSelectAllBtn" class="btn btn-sm btn-outline-primary me-1">Select All</button>
                    <button id="importDeselectAllBtn" class="btn btn-sm btn-outline-secondary">Deselect All</button>
                </div>
                <div id="importEventsList">
                    <div class="alert alert-info">Loading events...</div>
                </div>
            </div>
            <div class="modal-footer mt-3">
                <button type="button" id="importSelectedBtn" class="btn btn-primary btn-sm">Import Selected</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="eventForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="event_id">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" class="form-control form-control-sm" name="title" id="event_title" required>
                    </div>
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Start</label>
                                <input type="datetime-local" class="form-control form-control-sm" name="start"
                                    id="event_start" required>

                            </div>
                            <div class="col-md-6">
                                <label>End</label>
                                <input type="datetime-local" class="form-control form-control-sm" name="end"
                                    id="event_end">

                            </div>
                        </div>

                    </div>
                    <div class="mb-3 form-check">

                        <div class="row">
                            <div class="col-md-6">
                                <input type="checkbox" class="form-check-input " name="all_day" id="event_all_day">
                                <label class="form-check-label">All Day</label>
                            </div>
                            <div class="col-md-6"><label>Color</label>
                                <input type="color" class="form-control form-control-color  form-control-sm p-1"
                                    name="color" id="event_color" value="#7367F0">
                            </div>
                        </div>



                    </div>

                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Event Type</label>
                                <select class="form-control  form-control-sm" name="event_type" id="event_type">
                                    <option value="holiday">Holiday</option>
                                    <option value="exam">Exam</option>
                                    <option value="class">Class</option>
                                    <option value="sports">Sports</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="notice">Notice</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Event Scope</label>
                                <select class="form-control  form-control-sm" name="scope" id="event_scope">
                                    <option value="institution">Institution</option>
                                    <option value="personal">My Personal</option>
                                </select>
                            </div>




                        </div>


                    </div>
                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-danger btn-sm" id="deleteEvent">Delete</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
        </form>
    </div>
</div>


<?php require_once 'footer.php'; ?>




<script>
    function loadCurrentEvents() {
        fetch('calendar/events_now.php')
            .then(res => res.json())
            .then(data => {

                let box = document.getElementById('curEvents');

                if (data.length === 0) {
                    box.innerHTML = `<div class="alert alert-success">
                    No running events right now 🎉
                </div>`;
                    return;
                }

                let html = `<div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    Currently Running Events
                </div>
                <div class="list-group list-group-flush">`;

                data.forEach(e => {
                    html += `
                <div class="list-group-item">
                    <strong>${e.title}</strong>
                    <div class="small text-muted">
                        ${e.event_type} | ${e.scope.toUpperCase()}
                    </div>
                    <div class="text-primary small">
                        ${e.start} → ${e.end ?? 'Open'}
                    </div>
                </div>`;
                });

                html += `</div></div>`;
                box.innerHTML = html;
            });
    }

    loadCurrentEvents();
    setInterval(loadCurrentEvents, 60000); // প্রতি 1 মিনিটে রিফ্রেশ

</script>

<script>
    const eventColors = {
        holiday: '#ff3d57',
        exam: '#ff9f43',
        class: '#28c76f',
        sports: '#00cfe8',
        meeting: '#7367f0',
        notice: '#ea5455',
        other: '#6c757d'
    };




    function fcDateToInput(date) {
        if (!date) return '';
        let d = new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }


</script>

<script>

    document.addEventListener('DOMContentLoaded', function () {

        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return; // safeguard
        window.calendar = new FullCalendar.Calendar(calendarEl, {

            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridDay,dayGridWeek,dayGridMonth,listYear'
            },
            events: 'calendar/events_fetch.php', // session-based sccode inside PHP
            editable: true,
            selectable: true,
            selectMirror: true,

            eventContent: function (arg) {

                let type = arg.event.extendedProps.event_type;
                let scope = arg.event.extendedProps.scope;

                let scopeBadge = scope === 'personal'
                    ? '<span class="badge bg-primary ms-1">My</span>'
                    : '<span class="badge bg-success ms-1">Ins</span>';
                let color = eventColors[type] || '#6c757d';

                let typeBadge = `
                        <span class="badge ms-1" style="background:${color};color:white">
                            ${type}
                        </span>
                        `;
                return {
                    html: `
            <div class="fc-event-title">${arg.event.title}</div>
            <div style="font-size:11px;">
                ${typeBadge} ${scopeBadge}
            </div>
        `
                };
            },
            eventDidMount: function (info) {
                let now = new Date();
                let start = info.event.start;
                let end = info.event.end;

                if (start <= now && (!end || end >= now)) {
                    info.el.style.border = '3px solid pink';
                    info.el.style.boxShadow = '0 0 10px red';
                    info.el.classList.add('pulse-now');
                }
            },


            select: function (selectionInfo) {

                $('#event_id').val('');
                $('#event_title').val('');

                let start = selectionInfo.start;
                let end = selectionInfo.end;

                if (!selectionInfo.allDay && end) {
                    end = new Date(end.getTime() - 1000);
                }

                $('#event_start').val(fcDateToInput(start));
                $('#event_end').val(fcDateToInput(end));

                $('#event_all_day').prop('checked', selectionInfo.allDay);
                $('#event_color').val('#7367F0');
                $('#event_type').val('other');
                $('#event_scope').val('institution');

                $('#deleteEvent').hide();

                new bootstrap.Modal(document.getElementById('eventModal')).show();
            },
            eventClick: function (info) {

                $('#event_id').val(info.event.id);
                $('#event_title').val(info.event.title);
                $('#event_start').val(fcDateToInput(info.event.start));
                $('#event_end').val(fcDateToInput(info.event.end));
                $('#event_all_day').prop('checked', info.event.allDay);
                $('#event_color').val(info.event.backgroundColor);
                $('#event_type').val(info.event.extendedProps.event_type);
                $('#event_scope').val(info.event.extendedProps.scope);
                $('#deleteEvent').show();
                new bootstrap.Modal(document.getElementById('eventModal')).show();
            },
            eventDrop: function (info) { updateEvent(info.event); },
            eventResize: function (info) { updateEvent(info.event); }
        });

        calendar.render();

        // Add/Edit submit
        $('#eventForm').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();
            // var formData = $(this).serialize();

            if (!$('#event_color').val()) {
                $('#event_color').val(eventColors[$('#event_type').val()]);
            }

            // alert(JSON.stringify(formData));
            $.post('calendar/events_save.php', formData, function (res) {

                if (res.status == 'success') {
                    showToast('success', 'Event saved successfully!', 'Event');
                    calendar.refetchEvents();
                    bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                } else { alert('Error'); }
            }, 'json');
        });

        // Delete
        $('#deleteEvent').on('click', function () {
            if (confirm('Are you sure?')) {
                var id = $('#event_id').val();
                $.post('calendar/events_delete.php', { id: id }, function (res) {
                    if (res.status == 'success') {
                        calendar.refetchEvents();
                        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                    }
                }, 'json');
            }
        });

        function updateEvent(event) {
            $.post('calendar/events_save.php', {
                id: event.id,
                title: event.title,
                start: event.startStr,
                end: event.endStr,
                all_day: event.allDay ? 1 : 0,
                color: event.backgroundColor
            }, function (res) {
                if (res.status == 'success') calendar.refetchEvents();
            }, 'json');
        }

    });
</script>

<script>
    function loadTodayDashboard() {
        fetch('calendar/events_today.php')
            .then(r => r.json())
            .then(data => {

                // ---------- NEXT EVENT ----------
                if (data.next) {
                    let e = data.next;
                    document.getElementById('nextEventBox').innerHTML = `
                <div class="card border-primary shadow h-100">
                <div class="card-header bg-warning text-white">Next Event</div>
                    <div class="card-body">
                        <h4 class="card-title mt-3 fw-bold">${e.title}</h4>
                        <div class="text-primary fs-6">
                            ${e.start}
                        </div>
                        <span class="badge bg-info">${e.event_type}</span>
                        <span class="badge bg-${e.scope == 'personal' ? 'primary' : 'success'}">
                            ${e.scope}
                        </span>
                    </div>
                </div>`;
                } else {
                    document.getElementById('nextEventBox').innerHTML = `
                <div class="alert alert-success">No more events today 🎉</div>`;
                }

                // ---------- TODAY TIMELINE ----------
                let html = `<div class="card shadow">
            <div class="card-header bg-dark text-white">Today’s Schedule</div>
            <ul class="list-group list-group-flush">`;

                data.today.forEach(e => {
                    html += `
                <li class="list-group-item d-flex justify-content-between">
                    <div>
                        <strong>${e.title}</strong><br>
                        <small>${e.event_type} | ${e.scope}</small>
                    </div>
                    <div class="text-primary">
                        ${e.start.substr(11, 5)} → ${e.end ? e.end.substr(11, 5) : '∞'}
                    </div>
                </li>`;
                });

                html += `</ul></div>`;
                document.getElementById('todayTimeline').innerHTML = html;
            });
    }

    loadTodayDashboard();
    setInterval(loadTodayDashboard, 60000);

</script>




<script>
    $('#generalEntry').on('click', function (e) {
        e.preventDefault();

        // মডাল খোলা এবং ফিল্ড রিসেট
        $('#event_id').val('');
        $('#event_title').val('');
        $('#event_start').val('');
        $('#event_end').val('');
        $('#event_all_day').prop('checked', false);
        $('#event_color').val('#6c757d'); // default grey
        $('#event_type').val('other');
        $('#event_scope').val('institution'); // scope থাকবে institution
        $('#deleteEvent').hide();

        // একটি hidden input বা flag দিয়ে সেভ ফাইল জানাবে sccode=0
        if (!$('#is_general').length) {
            $('<input>').attr({
                type: 'hidden',
                id: 'is_general',
                name: 'is_general',
                value: 1
            }).appendTo('#eventForm');
        }

        new bootstrap.Modal(document.getElementById('eventModal')).show();
    });
</script>


<script>




    function loadGeneralEvents(type = '') {
        $.get('calendar/events_fetch.php', { sccode: 0, type: type }, function (data) {

            if (data.length === 0) {
                $('#importEventsList').html('<div class="alert alert-info">No events found.</div>');
                return;
            }

            let html = '<ul class="list-group">';

            data.forEach(e => {

                let badge = `<span class="badge bg-secondary ms-2">${e.event_type}</span>`;
                let rightSide = '';

                if (e.imported == 1) {
                    rightSide = `<span class="badge bg-success">Imported ✔</span>`;
                } else {
                    rightSide = `
                    <input type="checkbox"
                        class="form-check-input import-checkbox"
                        value="${e.id}"
                        data-event-type="${e.event_type}">
                `;
                }

                html += `
                <li class="list-group-item d-flex align-items-center">
                    
                    <!-- LEFT SIDE -->
                    <div class="flex-grow-1">
                        ${e.title} ${badge}
                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="ms-2">
                        ${rightSide}
                    </div>

                </li>
            `;
            });

            html += '</ul>';
            $('#importEventsList').html(html);

        }, 'json');
    }

    $(document).on('click', '#importEvents', function (e) {
        e.preventDefault();
        loadGeneralEvents('all');

        new bootstrap.Modal(document.getElementById('modalOfImport')).show();
    });


    // Type filter
    $('#importTypeFilter').on('change', function () {
        loadGeneralEvents($(this).val());
    });

    // Select/Deselect All
    $('#importSelectAllBtn').on('click', function () {
        $('.import-checkbox').prop('checked', true);
    });
    $('#importDeselectAllBtn').on('click', function () {
        $('.import-checkbox').prop('checked', false);
    });

    // Open modal


    // Import selected
    $('#importSelectedBtn').on('click', function () {
        let selectedIds = [];
        let types = {};
        $('.import-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
            types[$(this).val()] = $(this).data('event-type'); // keep event_type
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one event.');
            return;
        }

        $.post('calendar/events_save_general.php', { ids: selectedIds }, function (res) {
            if (res.status === 'success') {

                importModal.hide();

                calendar.refetchEvents();
                showToast('success', `${res.imported} event(s) imported!`, 'Import');
            } else {
                alert('Import failed!');
            }
        }, 'json');
    });








</script>

</body>

</html>