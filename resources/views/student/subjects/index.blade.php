@extends('layouts.student')

@section('content')

{{-- 1. الترويسة الأنيقة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #1e3c72, #2a5298); border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">موادي الدراسية 📚</h2>
            <p class="text-white-50 mb-0">تصفح مقرراتك، راجع الدروس، واستعد للاختبارات.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-book-open fa-4x text-light opacity-25"></i>
        </div>
    </div>
</div>

{{-- 2. كروت المواد --}}
<div class="row g-4">
    @forelse($subjects as $subject)
    <div class="col-md-4 col-sm-6">
        <div class="card h-100 shadow-sm border-0 hover-scale transition-all" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%;">
                    <i class="fas fa-book fa-2x"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">{{ $subject->name }}</h5>
                <p class="text-muted small mb-4">
                    <i class="fas fa-user-tie me-1"></i> المعلم: {{ $subject->teacher_name ?? 'غير محدد' }}
                </p>
                <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                    <i class="fas fa-door-open me-2"></i> دخول المادة
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow border-0 py-5 text-center" style="border-radius: 1rem;">
            <div class="card-body py-5">
                <i class="fas fa-folder-open fa-4x text-muted opacity-25 mb-3"></i>
                <h4 class="fw-bold text-secondary">لا توجد مواد مسجلة حالياً</h4>
                <p class="text-muted">لم يتم تسكينك في أي مواد دراسية بعد.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<style>
    .hover-scale:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

@endsection