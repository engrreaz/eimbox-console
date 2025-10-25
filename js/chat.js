let selectedContactId = null;

function loadUsers() {
    fetch('get_users.php')
        .then(res => res.json())
        .then(data => {
            const chatList = document.querySelector('#contact-list');
            chatList.innerHTML = '';
            data.forEach(user => {
                if(user.id == 1) return; // নিজেরা বাদ
                chatList.innerHTML += `
                <li class="chat-contact-list-item" data-userid="${user.id}">
                    <a class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar ${user.status}">
                            ${user.avatar ? `<img src="${user.avatar}" class="rounded-circle">` : `<span class="avatar-initial rounded-circle">${user.name[0]}</span>`}
                        </div>
                        <div class="chat-contact-info flex-grow-1 ms-4">
                            <h6 class="chat-contact-name m-0">${user.name}</h6>
                            <small class="chat-contact-status text-truncate">${user.role}</small>
                        </div>
                    </a>
                </li>
                `;
            });

            document.querySelectorAll('.chat-contact-list-item').forEach(item => {
                item.addEventListener('click', function(){
                    selectedContactId = this.dataset.userid;
                    loadMessages();
                });
            });
        });
}

function loadMessages() {
    if(!selectedContactId) return;
    fetch(`get_messages.php?contact_id=${selectedContactId}`)
        .then(res => res.json())
        .then(data => {
            const chatHistory = document.querySelector('.chat-history');
            chatHistory.innerHTML = '';
            data.forEach(msg => {
                const isRight = msg.sender_id == 1 ? 'chat-message-right' : '';
                chatHistory.innerHTML += `
                <li class="chat-message ${isRight}">
                    <div class="chat-message-wrapper flex-grow-1">
                        <div class="chat-message-text">
                            <p>${msg.message}</p>
                        </div>
                        <div class="text-body-secondary mt-1">
                            <small>${new Date(msg.created_at).toLocaleTimeString()}</small>
                        </div>
                    </div>
                </li>`;
            });
            chatHistory.scrollTop = chatHistory.scrollHeight;
        });
}

document.querySelector('.form-send-message').addEventListener('submit', function(e){
    e.preventDefault();
    if(!selectedContactId) return;
    const msg = document.querySelector('.message-input').value.trim();
    if(!msg) return;

    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg, receiver_id: selectedContactId })
    }).then(res => res.json()).then(data => {
        if(data.status==='success'){
            document.querySelector('.message-input').value='';
            loadMessages();
        }
    });
});

loadUsers();
setInterval(loadMessages, 3000);
