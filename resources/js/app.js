require('./bootstrap');

import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true
});

const userId = $('meta[name="user-id"]').attr('content');
const toUserId = $('#to-user-id').val();
let typingTimeout;

// Join the presence channel
window.Echo.join(`chat.${toUserId}`)
    .here((users) => {
        // List of users currently in the channel
        users.forEach(user => {
            console.log(user.name + ' is online');
        });
    })
    .joining((user) => {
        // Handle user joining
        console.log(user.name + ' joined the chat');
        $('#messages').append(`<div>${user.name} joined the chat</div>`);
    })
    .leaving((user) => {
        // Handle user leaving
        console.log(user.name + ' left the chat');
        $('#messages').append(`<div>${user.name} left the chat</div>`);
    })
    .listenForWhisper('typing', (e) => {
        // Handle typing event
        $('#typing-indicator').text(`${e.name} is typing...`);
        clearTimeout(typingTimeout);

        // Clear typing indicator after a delay
        typingTimeout = setTimeout(() => {
            $('#typing-indicator').text('');
        }, 3000);
    });

// Notify others when typing
$('#message-input').on('input', function() {
    window.Echo.join(`chat.${toUserId}`)
        .whisper('typing', {
            name: $('meta[name="user-name"]').attr('content')
        });
});

// Toggle chat widget
$('#toggle-chat').on('click', function() {
    const chatContent = $('#chat-content');
    const toggleButton = $('#toggle-chat');

    if (chatContent.is(':visible')) {
        chatContent.hide();
        toggleButton.text('+');
    } else {
        chatContent.show();
        toggleButton.text('−');
    }
});

// Send message
$('#send-button').click(function() {
    const message = $('#message-input').val();

    $.ajax({
        type: 'POST',
        url: '/send-message',
        data: {
            message: message,
            to_user_id: toUserId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            $('#message-input').val('');
        }
    });
});
