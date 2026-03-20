<?php require_once 'header.php'; ?>




<div class="container py-4">
  <div class="row g-0">
    <!-- Left -->
    <div class="col-lg-4 border-end">
      <div class="d-flex border-bottom">
        <h5 class="px-3 mb-3 flex-grow-1 text-success">My Tickets</h5>
        <button id="new-ticket-btn" class="btn btn-sm btn-success mx-3 mb-3">+ New Ticket</button>
      </div>

      <ul id="ticket-list" class="list-unstyled"></ul>
    </div>

    <!-- Right -->
    <div class="col-lg-8 d-flex flex-column" style="height:75vh;">
      <div id="ticket-header" class="border-bottom p-3 d-none">
        <div class="d-flex justify-content-between align-items-center">
          <h6 id="ticket-subject" class="mb-0"></h6>
          <span id="ticket-status" class="badge"></span>
        </div>
      </div>
      <div id="ticket-body" class="flex-grow-1 overflow-auto p-3" style="background: #fbf2fd">




      </div>

      <div id="ticket-body" class="flex-grow-1 overflow-auto p-3 " style="background:#fbf2fd">

        <div class="d-flex align-items-center justify-content-center" id="nomessageicon">
          <div class="text-center text-muted">
            <div class="mb-3" style="font-size:48px;">
              <i class="bi bi-chat-left-text"></i>
            </div>
            <div style="font-size:16px;">
              Select a ticket from the left to view the conversation
            </div>
          </div>
        </div>


      </div>


      <div id="ticket-footer" class="border-top p-3 d-none">
        <form id="ticket-form" class="d-flex">
          <input type="text" id="ticket-message" class="form-control me-2" placeholder="Type a message...">
          <button type="submit" class="btn btn-primary">Send</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Create Ticket Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Ticket</h5>
      </div>
      <div class="modal-body">
        <input type="text" id="ticket-subject-input" class="form-control mb-2" placeholder="Subject">
        <textarea id="ticket-message-input" class="form-control" rows="3"
          placeholder="Describe your issue..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="create-ticket-btn" class="btn btn-primary">Create</button>
      </div>
    </div>
  </div>
</div>


<?php require_once 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  // =============================
  // Session / user info
  // =============================
  let user_id = <?= json_encode($_SESSION['user_id'] ?? null); ?>;
  let userlevel = <?= json_encode($_SESSION['userlevel'] ?? null); ?>;
  let sccode = <?= json_encode($_SESSION['sccode'] ?? null); ?>;

  if (!user_id) {
    alert("User not logged in!");
  }

  // =============================
  // Ticket system variables
  // =============================
  let ticket_id = null;
  let ticketLimit = 10;

  // =============================
  // Load Tickets
  // =============================
  function loadTickets() {
    $("#ticket-list").load(
      `tickets/get_tickets.php?user_id=${user_id}&limit=${ticketLimit}`
    );
  }

  $(document).ready(function () {
    loadTickets();
  });

  // Load More button
  $(document).on("click", "#load-more", function () {
    ticketLimit += 3;
    loadTickets();
  });

  // =============================
  // Ticket click -> Load messages
  // =============================
  $(document).on("click", ".ticket-item", function () {
    ticket_id = $(this).data("id");
    let subject = $(this).find("span:first").text();
    let status = $(this).data("status");

    $("#ticket-subject").text(subject);
    $("#ticket-status")
      .text(status)
      .attr(
        "class",
        "badge bg-" +
        (status == "closed" ? "secondary" : status == "in_progress" ? "warning" : "success")
      );

    $("#ticket-header, #ticket-footer").removeClass("d-none");
    loadMessages();
  });

  // =============================
  // Load messages
  // =============================
  function loadMessages() {
    if (!ticket_id) return;
    $("#nomessageicon").addClass("d-none");
    $("#ticket-body").load(
      `tickets/get_ticket_messages.php?ticket_id=${ticket_id}&user_id=${user_id}`
    );
  }

  // Auto refresh messages every 3 seconds
  setInterval(loadMessages, 3000);

  // =============================
  // Send message
  // =============================
  $("#ticket-form").submit(function (e) {
    e.preventDefault();
    let msg = $("#ticket-message").val().trim();
    if (!msg) return;

    $.post("tickets/send_ticket_message.php", { ticket_id, user_id, message: msg }, function (res) {
      if (res === "closed") {
        alert("This ticket is closed. You can’t send messages.");
      } else if (res === "sent") {
        $("#ticket-message").val("");
        loadMessages();
      }
    });
  });

  // =============================
  // New Ticket Modal Show
  // =============================
  $("#new-ticket-btn").click(() => {
    const modalEl = document.getElementById("ticketModal");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  });

  // =============================
  // Create Ticket
  // =============================
  $("#create-ticket-btn").click(() => {
    let subject = $("#ticket-subject-input").val().trim();
    let message = $("#ticket-message-input").val().trim();

    if (!subject || !message) {
      alert("Please enter subject and message.");
      return;
    }

    $.post("tickets/create_ticket.php", { user_id, subject, message }, function (res) {
      if (res === "success") {
        const modalEl = document.getElementById("ticketModal");
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();

        // Clear form
        $("#ticket-subject-input").val("");
        $("#ticket-message-input").val("");

        // Reload tickets
        ticketLimit = 3; // reset limit
        loadTickets();
      } else {
        alert("Failed to create ticket!");
      }
    });
  });
</script>





</body>

</html>