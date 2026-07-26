@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row h-100">
        <!-- Users List -->
        <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm border-0 bg-body-tertiary">
                <div class="card-header bg-transparent border-bottom px-4 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-comments me-2 text-primary"></i>المحادثات</h5>
                </div>
                <div class="card-body p-0 overflow-auto" style="max-height: 70vh;">
                    <div class="list-group list-group-flush" id="users-list">
                        @foreach($users as $user)
                        <button type="button" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center user-select" data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                            <div class="avatar-circle bg-primary text-white d-flex justify-content-center align-items-center rounded-circle me-3 fw-bold" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div class="w-100 text-truncate">
                                <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                                <small class="text-muted">{{ __($user->role ?? 'مستخدم') }}</small>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-md-8 col-lg-9 h-100">
            <div class="card h-100 shadow-sm border-0 d-none flex-column" id="chat-box" style="height: 70vh !important;">
                <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex align-items-center">
                    <div class="avatar-circle bg-primary text-white d-flex justify-content-center align-items-center rounded-circle me-3 fw-bold" id="chat-user-avatar" style="width: 45px; height: 45px; font-size: 1.2rem;"></div>
                    <h5 class="mb-0 fw-bold" id="chat-user-name"></h5>
                </div>
                
                <div class="card-body overflow-auto p-4 flex-grow-1" id="messages-container" style="background-color: var(--bs-tertiary-bg); scroll-behavior: smooth;">
                    <!-- Messages will be loaded here -->
                </div>

                <div class="card-footer bg-transparent border-top p-3">
                    <form id="chat-form" class="d-flex align-items-center">
                        <input type="hidden" id="receiver-id">
                        <input type="text" id="message-input" class="form-control form-control-lg rounded-pill border-0 shadow-sm me-3 px-4" placeholder="اكتب رسالة..." required autocomplete="off" style="background-color: var(--bs-secondary-bg);">
                        <button type="submit" class="btn btn-primary rounded-circle shadow-sm d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;" id="send-btn">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card h-100 shadow-sm border-0 d-flex flex-column justify-content-center align-items-center text-center" id="empty-chat-box" style="height: 70vh !important;">
                <i class="fa-solid fa-comments text-muted mb-3" style="font-size: 4rem; opacity: 0.2;"></i>
                <h4 class="text-muted fw-normal">اختر مستخدماً لبدء المحادثة</h4>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .avatar-circle { transition: all 0.2s; }
    .user-select:hover .avatar-circle { transform: scale(1.05); }
    .user-select.active { background-color: var(--bs-primary-bg-subtle) !important; border-right: 4px solid var(--bs-primary); }
    .user-select.active .avatar-circle { background-color: var(--bs-primary) !important; color: white !important; }
    
    .message-bubble {
        max-width: 75%;
        padding: 12px 18px;
        border-radius: 20px;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .message-sent {
        background-color: var(--bs-primary);
        color: white;
        border-bottom-right-radius: 4px;
        align-self: flex-end;
    }
    .message-received {
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
        border-bottom-left-radius: 4px;
        align-self: flex-start;
    }
    
    [data-bs-theme="dark"] .message-received {
        background-color: #1e293b;
        color: #e2e8f0;
    }
    [data-bs-theme="dark"] .message-sent {
        background-color: #0284c7;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
    let currentReceiverId = null;
    const authId = {{ auth()->id() }};
    
    // Echo Setup for private channel
    Echo.private('chat.' + authId)
        .listen('MessageSent', (e) => {
            const message = e.message;
            // Only append if we are currently chatting with the sender
            if (currentReceiverId == message.sender_id) {
                appendMessage(message, false);
                scrollToBottom();
            } else {
                // Optional: Show a toast notification or badge if chatting with someone else
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'رسالة جديدة من ' + message.sender.name,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });

    $('.user-select').on('click', function() {
        $('.user-select').removeClass('active');
        $(this).addClass('active');
        
        currentReceiverId = $(this).data('id');
        const name = $(this).data('name');
        
        $('#receiver-id').val(currentReceiverId);
        $('#chat-user-name').text(name);
        $('#chat-user-avatar').text(name.substring(0, 1));
        
        $('#empty-chat-box').hide();
        $('#chat-box').removeClass('d-none').addClass('d-flex');
        
        loadMessages(currentReceiverId);
    });

    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        
        const messageInput = $('#message-input');
        const messageText = messageInput.val().trim();
        const receiverId = $('#receiver-id').val();
        
        if (!messageText || !receiverId) return;
        
        // Optimistic UI Append
        const tempMsg = {
            message: messageText,
            sender_id: authId,
            created_at: new Date().toISOString()
        };
        appendMessage(tempMsg, true);
        scrollToBottom();
        
        messageInput.val('');
        
        $.ajax({
            url: '{{ route("chat.send") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                receiver_id: receiverId,
                message: messageText
            },
            success: function(res) {
                // Message successfully sent
            },
            error: function(err) {
                console.error(err);
                alert('فشل إرسال الرسالة');
            }
        });
    });

    function loadMessages(userId) {
        const container = $('#messages-container');
        container.html('<div class="text-center mt-5"><div class="spinner-border text-primary" role="status"></div></div>');
        
        const fetchUrl = '{{ route("chat.messages", ":id") }}'.replace(':id', userId);
        
        $.get(fetchUrl, function(messages) {
            container.empty();
            if (messages.length === 0) {
                container.append('<div class="text-center text-muted mt-5">لا توجد رسائل سابقة. ابدأ المحادثة الآن!</div>');
                return;
            }
            
            const wrapper = $('<div class="d-flex flex-column gap-3"></div>');
            messages.forEach(msg => {
                const isSent = msg.sender_id == authId;
                const bubble = $(`
                    <div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}">
                        <div class="mb-1">${escapeHtml(msg.message)}</div>
                        <small style="font-size: 0.7rem; opacity: 0.7;">
                            ${new Date(msg.created_at).toLocaleTimeString('ar-EG', {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                `);
                wrapper.append(bubble);
            });
            container.append(wrapper);
            scrollToBottom();
        });
    }

    function appendMessage(msg, isSent) {
        const container = $('#messages-container > .d-flex');
        const bubble = $(`
            <div class="message-bubble ${isSent ? 'message-sent' : 'message-received'}">
                <div class="mb-1">${escapeHtml(msg.message)}</div>
                <small style="font-size: 0.7rem; opacity: 0.7;">
                    ${new Date(msg.created_at).toLocaleTimeString('ar-EG', {hour: '2-digit', minute:'2-digit'})}
                </small>
            </div>
        `);
        
        if (container.length === 0) {
            $('#messages-container').html('<div class="d-flex flex-column gap-3"></div>');
            $('#messages-container > .d-flex').append(bubble);
        } else {
            container.append(bubble);
        }
    }

    function scrollToBottom() {
        const container = $('#messages-container');
        container.scrollTop(container[0].scrollHeight);
    }
    
    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }
</script>
@endsection
