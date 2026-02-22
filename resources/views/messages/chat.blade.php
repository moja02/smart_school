@extends(
    Auth::user()->role == 'admin' ? 'layouts.admin' : 
    (Auth::user()->role == 'teacher' ? 'layouts.teacher' : 
    (Auth::user()->role == 'student' ? 'layouts.student' : 
    (Auth::user()->role == 'parent' ? 'layouts.parent' : 'layouts.app')))
)

@section('content')

<style>
    .chat-container { height: 75vh; background: #fff; border-radius: 1rem; overflow: hidden; display: flex; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); }
    .users-list { width: 300px; border-left: 1px solid #dee2e6; overflow-y: auto; background: #f8f9fa; }
    .user-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; align-items: center; text-decoration: none; color: #333; transition: background 0.2s; }
    .user-item:hover, .user-item.active { background: #e9ecef; }
    .user-avatar { width: 45px; height: 45px; border-radius: 50%; background: #e2e6ea; color: #495057; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; margin-left: 15px; flex-shrink: 0; }
    .chat-area { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .chat-header { padding: 15px 20px; border-bottom: 1px solid #dee2e6; background: #fff; display: flex; align-items: center; flex-shrink: 0; z-index: 10; }
    .messages-box { flex-grow: 1; padding: 20px; overflow-y: auto; background: #f4f6f9; display: flex; flex-direction: column; gap: 15px; }
    .messages-box.empty { justify-content: center; align-items: center; }
    .message-bubble { max-width: 70%; padding: 12px 18px; border-radius: 15px; position: relative; font-size: 0.95rem; line-height: 1.5; }
    .message-sent { align-self: flex-end; background: #0d6efd; color: #fff; border-bottom-left-radius: 2px; } 
    .message-received { align-self: flex-start; background: #fff; border: 1px solid #dee2e6; border-bottom-right-radius: 2px; }
    .message-time { font-size: 0.75rem; margin-top: 5px; opacity: 0.8; display: block; text-align: left; }
    .message-sent .message-time { color: #e0e0e0; }
    .input-area { padding: 20px; background: #fff; border-top: 1px solid #dee2e6; flex-shrink: 0; }
</style>

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">الرسائل والمحادثات 💬</h3>
            <p class="mb-0 opacity-75">تواصل مباشر مع أعضاء المدرسة.</p>
        </div>
    </div>
</div>

<div class="chat-container">
    {{-- القائمة الجانبية --}}
    <div class="users-list">
        
        {{-- ✅ خانة البحث الجديدة --}}
        <div class="p-3 border-bottom bg-white sticky-top" style="z-index: 5;">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="chatSearch" class="form-control bg-light border-0" placeholder="بحث عن اسم..." autocomplete="off">
            </div>
        </div>

        <div class="p-3 bg-light border-bottom fw-bold text-secondary">
            <i class="fas fa-users me-2"></i> الأشخاص
        </div>

        {{-- قائمة المستخدمين --}}
        <div id="usersContainer">
            @foreach($users as $user)
                {{-- تمت إضافة الكلاس search-item هنا --}}
                <a href="{{ route('messages.chat', $user->id) }}" class="user-item search-item {{ isset($receiver) && $receiver->id == $user->id ? 'active' : '' }}">
                    <div class="user-avatar shadow-sm">{{ substr($user->name, 0, 1) }}</div>
                    <div>
                        {{-- تمت إضافة الكلاس search-name هنا --}}
                        <div class="fw-bold text-dark search-name">{{ $user->name }}</div>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            @if($user->role == 'admin') <i class="fas fa-user-shield text-danger"></i> مدير 
                            @elseif($user->role == 'teacher') <i class="fas fa-chalkboard-teacher text-success"></i> معلم 
                            @elseif($user->role == 'student') <i class="fas fa-user-graduate text-primary"></i> طالب 
                            @else <i class="fas fa-user-friends text-warning"></i> ولي أمر @endif
                        </small>
                    </div>
                </a>
            @endforeach
        </div>
        {{-- رسالة عند عدم وجود نتائج --}}
        <div id="noResults" class="text-center p-4 text-muted d-none">
            <small>لا توجد نتائج مطابقة</small>
        </div>
    </div>

    {{-- منطقة المحادثة --}}
    <div class="chat-area">
        @if(isset($receiver))
            <div class="chat-header shadow-sm">
                <div class="user-avatar bg-primary text-white">{{ substr($receiver->name, 0, 1) }}</div>
                <div class="me-3">
                    <h5 class="m-0 fw-bold">{{ $receiver->name }}</h5>
                    <small class="text-success"><i class="fas fa-circle" style="font-size: 8px;"></i> متاح للمحادثة</small>
                </div>
            </div>

            <div class="messages-box {{ $messages->count() == 0 ? 'empty' : '' }}" id="msgBox">
                @forelse($messages as $msg)
                    <div class="message-bubble shadow-sm {{ $msg->sender_id == Auth::id() ? 'message-sent' : 'message-received' }}">
                        {{ $msg->message }}
                        <span class="message-time">{{ $msg->created_at->format('h:i A') }}</span>
                    </div>
                @empty
                    <div class="text-center text-muted">
                        <div class="mb-3"><i class="fas fa-comments fa-4x text-secondary opacity-25"></i></div>
                        <h5 class="fw-bold">لا توجد رسائل سابقة</h5>
                        <p class="small">ابدأ المحادثة مع <strong>{{ $receiver->name }}</strong> الآن!</p>
                    </div>
                @endforelse
            </div>

            <div class="input-area">
                <form action="{{ route('messages.send') }}" method="POST" class="d-flex gap-2 align-items-center">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">
                    <input type="text" name="message" class="form-control form-control-lg rounded-pill border-1" placeholder="اكتب رسالتك هنا..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary rounded-circle shadow hover-scale" style="width: 50px; height: 50px; flex-shrink: 0;"><i class="fas fa-paper-plane fs-5"></i></button>
                </form>
            </div>
        @else
            <div class="d-flex flex-column justify-content-center align-items-center h-100 bg-light">
                <div class="p-5 rounded-circle bg-white shadow-sm mb-4"><i class="fas fa-paper-plane fa-4x text-primary opacity-50"></i></div>
                <h3 class="text-dark fw-bold">أهلاً بك في المحادثات 👋</h3>
                <p class="text-muted">اختر شخصاً من القائمة الجانبية لبدء التواصل.</p>
            </div>
        @endif
    </div>
</div>

{{-- ✅ سكربت البحث والتمرير التلقائي --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. التمرير لأسفل المحادثة تلقائياً
        var msgBox = document.getElementById("msgBox");
        if(msgBox) { msgBox.scrollTop = msgBox.scrollHeight; }

        // 2. تفعيل البحث الفوري
        var searchInput = document.getElementById('chatSearch');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                var filter = this.value.toLowerCase();
                var items = document.querySelectorAll('.search-item');
                var noResults = document.getElementById('noResults');
                var visibleCount = 0;

                items.forEach(function(item) {
                    var name = item.querySelector('.search-name').innerText.toLowerCase();
                    if(name.includes(filter)) {
                        item.style.display = "flex"; // إظهار
                        visibleCount++;
                    } else {
                        item.style.display = "none"; // إخفاء
                    }
                });

                // إظهار رسالة "لا توجد نتائج" إذا تم إخفاء الجميع
                if(visibleCount === 0) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            });
        }
    });
</script>

@endsection