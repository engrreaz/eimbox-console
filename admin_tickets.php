<?php require_once 'header.php'; ?>

<style>
  .ticket-item {
    cursor: pointer;
    transition: background 0.2s;
  }

  .ticket-item:hover {
    background: #f8f9fa;
  }
</style>

<div class="container py-4">
  <div class="row g-0">
    <!-- Sidebar -->
    <div class="col-lg-4 border-end">
      <h5 class="px-3 mb-3">All Tickets</h5>
      <ul id="admin-ticket-list" class="list-unstyled"></ul>
    </div>

    <!-- Chat Panel -->
    <div class="col-lg-8 d-flex flex-column" style="height:70vh;">
      <div id="admin-ticket-header" class="border-bottom p-3 d-none">
        <div class="d-flex justify-content-between align-items-center">
          <h6 id="admin-ticket-subject" class="mb-0 flex-grow-1"></h6>
          <div class="me-3">
            <select id="ticket-category" class="form-select form-select-sm d-inline-block w-auto">
              <option value="General">General</option>
              <option value="Technical">Technical</option>
              <option value="Billing">Billing</option>
              <option value="Bug Fix">Bug Fix</option>
              <option value="Knowledge Base">Knowledge Base</option>
              <option value="Request">Request</option>

            </select>
          </div>
          <div class="me-3">
            <select id="ticket-priority" class="form-select form-select-sm d-inline-block w-auto">
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
              <option value="Critical">Critical</option>
              <option value="Hot_fix">Hot Fix</option>
            </select>
          </div>
          <div>
            <select id="ticket-status-select" class="form-select form-select-sm d-inline-block w-auto">
              <option value="open">Open</option>
              <option value="waiting">Waiting</option>
              <option value="replied">Replied</option>
              <option value="in_progress">In Progress</option>
              <option value="hold">Hold</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
          </div>
        </div>
      </div>

      <div id="admin-ticket-body" class="flex-grow-1 overflow-auto p-3 bg-light"></div>

      <div id="admin-ticket-footer" class="border-top p-3 d-none">
        <form id="admin-message-form" class="d-flex">
          <input type="text" id="admin-message" class="form-control me-2" placeholder="Reply to user...">
          <button type="submit" class="btn btn-primary">Send</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true"
  data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Message Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="msgid" class="form-control" readonly>
        <textarea id="modalMessageText" class="form-control" rows="8"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Submit Dev Note</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  let admin_id = <?= json_encode($_SESSION['user_id']); ?>;
  let is_admin = <?= $is_admin; ?>;
  let ticket_id = null;
  let ticketLimit = 5;
  let refreshTimer = null;

  // ✅ Load tickets list
  function loadAdminTickets() {
    $("#admin-ticket-list").load("tickets/admin_get_tickets.php?limit=" + ticketLimit);
  }

  $(document).ready(function () {
    loadAdminTickets();
  });

  // ✅ Load more
  $(document).on("click", "#load-more", function () {
    ticketLimit += 5;
    loadAdminTickets();
  });

  // ✅ Ticket click
  $(document).on("click", ".ticket-item", function () {
    ticket_id = $(this).data("id");
    let status = $(this).data("status");
    let category = $(this).data("category");
    let priority = $(this).data("priority");
    let subject = $(this).data("subject");


    $("#admin-ticket-subject").text(subject);
    $("#admin-ticket-header, #admin-ticket-footer").removeClass("d-none");
    $("#ticket-status-select").val(status);
    $("#ticket-category").val(category);
    $("#ticket-priority").val(priority);
    loadAdminMessages();

    clearInterval(refreshTimer);
    refreshTimer = setInterval(() => {
      if (!$("#messageModal").hasClass("show")) {
        loadAdminMessages();
      }
    }, 4000);
  });

  // ✅ Load messages safely
  function loadAdminMessages() {
    if (ticket_id) {
      $.get(`tickets/admin_get_messages.php?ticket_id=${ticket_id}`, function (data) {
        $("#admin-ticket-body").html(data);
      });
    }
  }

  // ✅ Send message
  $("#admin-message-form").submit(function (e) {
    e.preventDefault();
    let msg = $("#admin-message").val().trim();
    if (!msg) return;

    $.post("tickets/admin_send_message.php",
      { ticket_id, sender_id: admin_id, message: msg },
      function (res) {
        if (res === "closed") {
          alert("This ticket is closed. Cannot send messages.");
        } else if (res === "sent") {
          $("#admin-message").val("");
          loadAdminMessages();
        }
      }
    );
  });

  // ✅ Change status
  $("#ticket-status-select").change(function () {
    let newStatus = $(this).val();
    if (!ticket_id) return;

    $.post("tickets/admin_update_status.php",
      { ticket_id, status: newStatus, tail: 'status' },
      function (res) {
        if (res === "updated") {
          loadAdminTickets();
        }
      }
    );
  });

  $("#ticket-category").change(function () {
    let newStatus = $(this).val();
    if (!ticket_id) return;

    $.post("tickets/admin_update_status.php",
      { ticket_id, status: newStatus, tail: 'category' },
      function (res) {
        if (res === "updated") {
          loadAdminTickets();
        }
      }
    );
  });

  $("#ticket-priority").change(function () {
    let newStatus = $(this).val();
    if (!ticket_id) return;

    $.post("tickets/admin_update_status.php",
      { ticket_id, status: newStatus, tail: 'priority' },
      function (res) {
        if (res === "updated") {
          loadAdminTickets();
        }
      }
    );
  });

  // ✅ Modal open safely (only one instance)
  let messageModal = new bootstrap.Modal(document.getElementById("messageModal"));

  $(document).on("click", ".message-item", function (e) {
    e.stopPropagation(); // prevent bubbling
    const msg = $(this).data("message") || "";
    const ticket_id = $(this).data("id");
    const dev_sub = $(this).data("dev");

    if (dev_sub == 1) {
      alert('Note already submitted to developer.');
      return;
    }
    $("#modalMessageText").val(msg);
    $("#msgid").val(ticket_id);
    messageModal.show();
  });

  // ✅ Prevent modal from closing when textarea clicked
  $("#modalMessageText").on("click", function (e) {
    e.stopPropagation();
  });

</script>


<script>
  $(document).on("click", ".btn-primary[data-bs-dismiss='modal']", function () {
    let notes = $("#modalMessageText").val().trim();
    let msgid = $("#msgid").val();

    if (!notes) return alert("Please write something before submitting.");

    $.post("tickets/save_devnote.php",
      { ticket_id: ticket_id, notes: notes, msgid: msgid },
      function (res) {
        if (res === "success") {
          alert("Dev Note saved successfully!");
          $("#modalMessageText").val("");
        } else {
          alert("Failed to save note: " + res);
        }
      }
    );
  });

</script>
</body>

</html>