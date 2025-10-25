function debounce(func, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

document.addEventListener("DOMContentLoaded", () => {

    const currentUserId = 1; // লগইন ইউজার
    let selectedContactId = null;

    const chatHistoryBody = document.querySelector('.chat-history-body');
    const chatHistoryList = document.querySelector('.chat-history');
    const messageInput = document.querySelector('.message-input');
    const formSendMessage = document.querySelector('.form-send-message');

    // Load Users
    function loadUsers() {
        fetch('get_users.php')
            .then(res => res.json())
            .then(users => {
                const chatList = document.querySelector('#contact-list');
                chatList.innerHTML = '';
                users.forEach(user => {
                    chatList.innerHTML += `
                    <li class="chat-contact-list-item" data-userid="${user.id}">
                        <a class="d-flex align-items-center cursor-pointer">
                            <div class="flex-shrink-0 avatar ${user.status}">
                                ${user.avatar ? `<img src="${user.avatar}" class="rounded-circle">` : `<span class="avatar-initial rounded-circle">${user.name[0]}</span>`}
                            </div>
                            <div class="chat-contact-info flex-grow-1 ms-4">
                                <h6 class="chat-contact-name m-0">${user.name}</h6>
                                <small class="chat-contact-status text-truncate">${user.role}</small>
                            </div>
                        </a>
                    </li>`;
                });

                document.querySelectorAll('.chat-contact-list-item').forEach(item => {
                    item.addEventListener('click', () => {
                        selectedContactId = item.dataset.userid;
                        document.querySelector('.app-chat-history').classList.remove('d-none');
                        loadMessages();
                    });
                });
            });
    }

    // Load Messages
    function loadMessages() {
        if (!selectedContactId) return;
        fetch(`get_messages.php?contact_id=${selectedContactId}`)
            .then(res => res.json())
            .then(messages => {
                chatHistoryList.innerHTML = '';
                messages.forEach(msg => {
                    const isRight = msg.sender_id == currentUserId ? 'chat-message-right' : '';
                    chatHistoryList.innerHTML += `
                    <li class="chat-message ${isRight}">
                        <div class="d-flex overflow-hidden">
                            ${isRight ? `
                                <div class="chat-message-wrapper flex-grow-1">
                                    <div class="chat-message-text">
                                        <p class="mb-0">${msg.message}</p>
                                    </div>
                                    <div class="text-end text-body-secondary mt-1">
                                        <small>${new Date(msg.created_at).toLocaleTimeString()}</small>
                                    </div>
                                </div>
                                <div class="user-avatar flex-shrink-0 ms-4">
                                    <div class="avatar avatar-sm">
                                        <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/1.png" class="rounded-circle">
                                    </div>
                                </div>` : `
                                <div class="user-avatar flex-shrink-0 me-4">
                                    <div class="avatar avatar-sm">
                                        <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/4.png" class="rounded-circle">
                                    </div>
                                </div>
                                <div class="chat-message-wrapper flex-grow-1">
                                    <div class="chat-message-text">
                                        <p class="mb-0">${msg.message}</p>
                                    </div>
                                    <div class="text-body-secondary mt-1">
                                        <small>${new Date(msg.created_at).toLocaleTimeString()}</small>
                                    </div>
                                </div>`}
                        </div>
                    </li>`;
                });
                chatHistoryBody.scrollTop = chatHistoryBody.scrollHeight;
            });
    }

    // Send Message
    formSendMessage.addEventListener('submit', e => {
        e.preventDefault();
        if (!selectedContactId) return;
        const msg = messageInput.value.trim();
        if (!msg) return;

        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg, receiver_id: selectedContactId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                messageInput.value = '';
                loadMessages();
            }
        });
    });

    // Initial Load
    loadUsers();
    setInterval(() => { if(selectedContactId) loadMessages(); }, 3000); // রিয়েল-টাইম আপডেট
});
