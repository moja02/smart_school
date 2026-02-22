@extends(
    Auth::user()->role == 'admin' ? 'layouts.admin' : 
    (Auth::user()->role == 'manager' ? 'layouts.manager' :  
    (Auth::user()->role == 'teacher' ? 'layouts.teacher' : 
    (Auth::user()->role == 'student' ? 'layouts.student' : 
    (Auth::user()->role == 'parent' ? 'layouts.parent' : 'layouts.admin')))) 
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
    .input-area { 
        padding: 20px;
        background: #fff;
        border-top: 1px solid #dee2e6;
        flex-shrink: 0;
    }
    .badge.x-small {
    padding: 0.35em 0.65em;
    font-weight: 500;
    }
    .user-item.active {
        border-right: 4px solid #0d6efd; /* تمييز المستخدم النشط */
    }
    .user-avatar {
        background: linear-gradient(45deg, #6c757d, #adb5bd) !important;
        color: white !important;
    }
    /* إذا كان المستخدم مديراً، نغير لون الأفاتار الخاص به للتميز */
    .user-item[data-role="manager"] .user-avatar {
        background: linear-gradient(45deg, #dc3545, #ff4757) !important;
    }
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
                <a href="{{ route('messages.chat', $user->id) }}" class="user-item {{ isset($receiver) && $receiver->id == $user->id ? 'active' : '' }} search-item">
            <div class="user-avatar shadow-sm">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div class="ms-3 flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 search-name">{{ $user->name }}</h6>
                    
                    {{-- إضافة الشارات الملونة هنا --}}
                    @php
                        $badgeClass = [
                            'manager' => 'bg-danger',
                            'admin'   => 'bg-primary',
                            'teacher' => 'bg-success',
                            'parent'  => 'bg-info text-dark',
                            'student' => 'bg-warning text-dark'
                        ][$user->role] ?? 'bg-secondary';

                        $roleName = [
                            'manager' => 'المدير',
                            'admin'   => 'الإدارة',
                            'teacher' => 'معلم',
                            'parent'  => 'ولي أمر',
                            'student' => 'طالب'
                        ][$user->role] ?? 'مستخدم';
                    @endphp
                    <span class="badge {{ $badgeClass }} rounded-pill x-small shadow-sm" style="font-size: 0.7rem;">
                        {{ $roleName }}
                    </span>
                </div>
                
                @if($user->last_message)
                    <small class="text-muted d-block text-truncate" style="max-width: 150px;">{{ $user->last_message }}</small>
                @endif
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
            <div class="chat-header p-3 border-bottom bg-white d-flex align-items-center shadow-sm">
                <div class="user-avatar me-3">
                    {{ mb_substr($receiver->name, 0, 1) }}
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">{{ $receiver->name }}</h5>
                    <small class="text-muted">
                        <i class="fas fa-circle text-success small"></i> 
                        {{-- عرض الرتبة في الهيدر --}}
                        {{ $roleName }} 
                    </small>
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