<?php require_once 'header.php'; ?>

<div class="container py-4">
  <div class="row g-0">
    <!-- Sidebar -->
    <div class="col-lg-4 border-end">
      <h5 class="px-3 mb-3">All Tickets</h5>
      <ul id="admin-ticket-list" class="list-unstyled"></ul>
    </div>

    <!-- Chat Panel -->
    <div class="col-lg-8 d-flex flex-column" style="height:80vh;">
      <div id="admin-ticket-header" class="border-bottom p-3 d-none">
        <div class="d-flex justify-content-between align-items-center">
          <h6 id="admin-ticket-subject" class="mb-0"></h6>
          <div>
            <select id="ticket-status-select" class="form-select form-select-sm d-inline-block w-auto">
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// let admin_id = <?php echo $user_id_no;?>; // Suppose 99 = admin
let admin_id = 1; // Suppose 99 = admin
let ticket_id = null;

// Load all tickets
function loadAdminTickets() {
  $("#admin-ticket-list").load("tickets/admin_get_tickets.php");
}
loadAdminTickets();

// Ticket click
$(document).on("click", ".ticket-item", function () {
  ticket_id = $(this).data("id");
  let status = $(this).data("status");
  let subject = $(this).find("strong").next().text().trim();

  $("#admin-ticket-subject").text(subject);
  $("#admin-ticket-header, #admin-ticket-footer").removeClass("d-none");
  $("#ticket-status-select").val(status);
  loadAdminMessages();
});

// Load messages
function loadAdminMessages() {
  if (ticket_id) {
    $("#admin-ticket-body").load(`tickets/admin_get_messages.php?ticket_id=${ticket_id}`);
  }
}
setInterval(loadAdminMessages, 3000);

// Send message
$("#admin-message-form").submit(function (e) {
  e.preventDefault();
  let msg = $("#admin-message").val();
  if (!msg.trim()) return;

  $.post("tickets/admin_send_message.php", { ticket_id, sender_id: admin_id, message: msg }, function (res) {
    if (res === "closed") {
      alert("This ticket is closed. Cannot send messages.");
    } else if (res === "sent") {
      $("#admin-message").val("");
      loadAdminMessages();
    }
  });
});

// Change status
$("#ticket-status-select").change(function () {
  let newStatus = $(this).val();
  $.post("tickets/admin_update_status.php", { ticket_id, status: newStatus }, function (res) {
    if (res === "updated") {
      loadAdminTickets();
    }
  });a
});
</script>


<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>
</html>