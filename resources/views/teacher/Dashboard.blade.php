@extends('layouts.teacher')

@section('content')
<div class="container-fluid py-4">
    
    {{-- رسالة الترحيب --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">مرحباً، {{ Auth::user()->name }} 👋</h2>
                        <p class="mb-0 opacity-75">نتمنى لك يوماً دراسياً موفقاً!</p>
                    </div>
                    <div class="d-none d-md-block opacity-50">
                        <i class="fas fa-chalkboard-teacher fa-4x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- بطاقات الإحصائيات --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold">فصولي الدراسية</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ $classes->count() }}</h2>
                        </div>
                        <div class="bg-light text-primary rounded-circle p-3">
                            <i class="fas fa-chalkboard fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route('teacher.classes') }}" class="btn btn-link text-decoration-none p-0 mt-3 small">عرض التفاصيل <i class="fas fa-arrow-left"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold">إجمالي الطلاب</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ $studentsCount }}</h2>
                        </div>
                        <div class="bg-light text-success rounded-circle p-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <span class="text-muted small mt-3 d-block">موزعين على الفصول</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold">المواد المسندة</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ $subjectsCount }}</h2>
                        </div>
                        <div class="bg-light text-warning rounded-circle p-3">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                    <span class="text-muted small mt-3 d-block">مواد نشطة</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- الوصول السريع للفصول --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-door-open text-primary me-2"></i> وصول سريع للفصول</h5>
                </div>
                <div class="card-body">
                    @if($classes->count() > 0)
                        <div class="row g-3">
                            @foreach($classes->take(4) as $class)
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light hover-shadow transition-all d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $class->name }}</h6>
                                        <small class="text-muted">{{ $class->students_count ?? $class->students->count() }} طالب</small>
                                    </div>
                                    <a href="{{ route('teacher.class', $class->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                        دخول <i class="fas fa-arrow-left small"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-center">
                            <a href="{{ route('teacher.classes') }}" class="btn btn-outline-secondary btn-sm rounded-pill">عرض كل الفصول</a>
                        </div>
                    @else
                        <div class="text-center py-4 opacity-50">
                            <i class="fas fa-chalkboard fa-3x mb-2"></i>
                            <p>لا توجد فصول مسندة لك حالياً.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- التنبيهات والرسائل --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-danger"><i class="fas fa-bell me-2"></i> تنبيهات جديدة</h5>
                    <span class="badge bg-danger rounded-pill">{{ $recentMessages->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentMessages as $msg)
                        <a href="{{ route('messages.chat', $msg->sender_id) }}" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold">{{ $msg->sender->name ?? 'مستخدم' }}</h6>
                                <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-truncate text-muted small">{{ $msg->content }}</p>
                        </a>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                            <p class="mb-0">لا توجد رسائل جديدة.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; transform: translateY(-2px); }
    .transition-all { transition: all 0.3s ease; }
</style>
@endsection