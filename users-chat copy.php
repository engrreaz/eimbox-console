<?php require_once 'header.php'; ?>




<div class="container py-4">
  <div class="row g-0">
    <!-- Left -->
    <div class="col-lg-4 border-end">
      <h5 class="px-3 mb-3">My Tickets</h5>
      <div> (in Tab Panel) Tickets | Chat | Contacts</div>
      <div> (status) New / waiting reply / replied/in progress / hold/resolved</div>
      <div> (icategory) Tech / billing / knowledge base / request </div>
      <div> (priority) Low / Medium / High / Critical / Hot Fix </div>
      <div> ticket close by user | ticket status update | ticket description fixed thakbe. then chat | description / ticket boro hole split korar bebostha korte hobe</div>
      <div> Message Read Time / Send Time /  </div>
      <button id="new-ticket-btn" class="btn btn-sm btn-success mx-3 mb-3">+ New Ticket</button>
      <ul id="ticket-list" class="list-unstyled"></ul>
    </div>

    <!-- Right -->
    <div class="col-lg-8 d-flex flex-column" style="height:80vh;">
      <div id="ticket-header" class="border-bottom p-3 d-none">
        <div class="d-flex justify-content-between align-items-center">
          <h6 id="ticket-subject" class="mb-0"></h6>
          <span id="ticket-status" class="badge"></span>
        </div>
      </div>
      <div id="ticket-body" class="flex-grow-1 overflow-auto p-3 bg-light"></div>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">New Ticket</h5></div>
      <div class="modal-body">
        <input type="text" id="ticket-subject-input" class="form-control mb-2" placeholder="Subject">
        <textarea id="ticket-message-input" class="form-control" rows="3" placeholder="Describe your issue..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="create-ticket-btn" class="btn btn-primary">Create</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let user_id = 1; // demo user
let ticket_id = null;

function loadTickets() {
  $("#ticket-list").load("tickets/get_tickets.php?user_id=" + user_id);
}
loadTickets();

// Ticket click
$(document).on("click", ".ticket-item", function () {
  ticket_id = $(this).data("id");
  let subject = $(this).find("span:first").text();
  let status = $(this).data("status");

  $("#ticket-subject").text(subject);
  $("#ticket-status").text(status).attr("class", "badge bg-" + (status=="closed"?"secondary":status=="in_progress"?"warning":"success"));
  $("#ticket-header, #ticket-footer").removeClass("d-none");
  loadMessages();
});

// Load messages
function loadMessages() {
  if (ticket_id) {
    $("#ticket-body").load(`tickets/get_ticket_messages.php?ticket_id=${ticket_id}&user_id=${user_id}`);
  }
}
setInterval(loadMessages, 3000);

// Send message
$("#ticket-form").submit(function(e){
  e.preventDefault();
  let msg = $("#ticket-message").val();
  if (!msg.trim()) return;

  $.post("tickets/send_ticket_message.php", { ticket_id, user_id, message: msg }, function(res){
    if (res === "closed") {
      alert("This ticket is closed. You can’t send messages.");
    } else if (res === "sent") {
      $("#ticket-message").val("");
      loadMessages();
    }
  });
});

// New Ticket Modal
$("#new-ticket-btn").click(()=> $("#ticketModal").modal("show"));

$("#create-ticket-btn").click(()=>{
  let subject = $("#ticket-subject-input").val();
  let message = $("#ticket-message-input").val();
  $.post("tickets/create_ticket.php", { user_id, subject, message }, function(res){
    if(res === "success"){
      $("#ticketModal").modal("hide");
      loadTickets();
    }
  });
});
</script>


















<!-- Content -->

<div class="container-fluid py-4">
  <div class="row g-0">
    <!-- Left Sidebar -->
    <div class="col-lg-3 border-end">
      <h5 class="px-3 mb-3">Contacts</h5>
      <ul id="contact-list" class="list-unstyled m-0 p-0"></ul>
    </div>

    <!-- Chat Section -->
    <div class="col-lg-9 d-flex flex-column" style="height:80vh;">
      <div id="chat-header" class="border-bottom p-3 d-none">
        <h6 id="chat-user-name" class="mb-0"></h6>
      </div>
      <div id="chat-body" class="flex-grow-1 p-3 overflow-auto bg-light"></div>
      <div id="chat-footer" class="border-top p-3 d-none">
        <form id="chat-form" class="d-flex">
          <input type="text" id="message" class="form-control me-2" placeholder="Type a message...">
          <button type="submit" class="btn btn-primary">Send</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let sender_id = 1; // demo logged user
let receiver_id = null;

// Load user list
function loadUsers() {
  $("#contact-list").load("tickets/get_users.php");
}
loadUsers();

// Click contact to open chat
$(document).on("click", ".chat-contact-item", function () {
  receiver_id = $(this).data("id");
  let name = $(this).find(".fw-bold").text();
  $("#chat-user-name").text(name);
  $("#chat-header, #chat-footer").removeClass("d-none");
  loadMessages();
});

// Load chat messages
function loadMessages() {
  if (receiver_id) {
    $("#chat-body").load(`tickets/get_messages.php?sender=${sender_id}&receiver=${receiver_id}`);
  }
}

// Auto refresh every 3 seconds
setInterval(loadMessages, 3000);

// Send message
$("#chat-form").submit(function (e) {
  e.preventDefault();
  let msg = $("#message").val();
  if (msg.trim() === "") return;

  $.post("tickets/send_message.php", { sender: sender_id, receiver: receiver_id, message: msg }, function (res) {
    if (res === "sent") {
      $("#message").val("");
      loadMessages();
    }
  });
});
</script>


<!-- / Content -->

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>