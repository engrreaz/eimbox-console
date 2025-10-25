const notifListEl = document.getElementById('notifList');
const notifCountBadge = document.getElementById('notifCountBadge');
const markAllReadBtn = document.getElementById('markAllRead');
let notifTimer = null;

// escape HTML
function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// render notifications
function renderNotifications(data) {
  if (!data) return;

  const notifications = data.notifications || [];
  const unread = data.unread_count || 0;

  // badge update
  if (unread > 0) {
    notifCountBadge.style.display = 'inline-block';
    notifCountBadge.textContent = unread + ' New';
    document.getElementById('dotBadge').classList.toggle('bg-gray');

  } else {
    notifCountBadge.style.display = 'none';
  }

  // list update
  if (notifications.length === 0) {
    notifListEl.innerHTML = '<div class="p-3 text-center text-muted">No notifications</div>';
    return;
  }

  notifListEl.innerHTML = notifications.map(n => {
    const readClass = n.is_read === "0" ? 'fw-bold' : 'text-muted';
    const title = escapeHtml(n.title);
    const msg = escapeHtml(n.message);
    const time = n.created_ago;
    const noti_link = n.link;




    return `

    <li data-id="${n.id}"  data-bs-link="${noti_link}" class="list-group-item list-group-item-action dropdown-notifications-item notif-item">
        
    <div class="d-flex">
            <div class="flex-shrink-0 me-3">
                <div class="avatar">
                    <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/1.png"
                        alt="alt" class="rounded-circle" />
                </div>
            </div>
            <div class="flex-grow-1">
                <h6 class="small mb-50">${title}</h6>
                <small class="mb-1 d-block text-body">${msg}</small>
                <small class="text-body-secondary">${time}</small>
            </div>
            <div class="flex-shrink-0 dropdown-notifications-actions">
                <a href="javascript:void(0)" class="dropdown-notifications-read"> <span
                        class="badge badge-dot"></span></a>
                <a href="javascript:void(0)" class="dropdown-notifications-archive"> <span
                        class="icon-base ri ri-close-line"></span></a>
            </div>
        </div>
    </li>


                            
      
    `;
  }).join('');

  document.querySelectorAll('.notif-item').forEach(el => {
    el.addEventListener('click', function () {
      const notifId = this.getAttribute('data-id');
      const notifLink = this.getAttribute('data-bs-link');

      el.closest('li').style.display = 'none';
      // প্রথমে read mark করো
      markAsRead(notifId);

      // তারপর লিংক ওপেন করো (স্মুথ টাইমিং রাখতে সামান্য delay)
      if (notifLink && notifLink !== '#') {
        setTimeout(() => {
          // window.location.href = notifLink;
          window.open(notifLink, '_blank');
        }, 100);
      }
    });
  });
}


// fetch notifications
function loadNotifications() {
  fetch('get_notifications.php?limit=10', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {

      if (data.error) {
        console.error('Notification error:', data.error);
        return;
      }
      console.log("Notifications fetched:", data);
      renderNotifications(data);
    })
    .catch(err => console.error('Notification fetch failed', err));
}

// mark one as read
function markAsRead(notificationId) {
  // alert(notificationId);
  fetch('mark_read.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ notification_id: parseInt(notificationId, 10) })
  }).then(r => r.json()).then(resp => {
    loadNotifications();
  }).catch(err => console.error(err));
}

// mark all as read
function markAllAsRead() {
  fetch('mark_read.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ all: 1 })
  }).then(r => r.json()).then(resp => {
    loadNotifications();
  }).catch(err => console.error(err));
}

// attach mark all read
if (markAllReadBtn) {
  markAllReadBtn.addEventListener('click', function (e) {
    e.preventDefault();
    markAllAsRead();
  });
}

// initial load + poll every 8s
document.addEventListener('DOMContentLoaded', function () {
  loadNotifications();
  if (notifTimer) clearInterval(notifTimer);
  notifTimer = setInterval(loadNotifications, 100000);
});

