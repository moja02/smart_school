@extends('layouts.student')

@section('content')

@php
    $totalUpcomingExams = $subjects->sum(function($subject) {
        return $subject->upcoming_exams->count() ?? 0;
    });
@endphp

{{-- 1. الترويسة باللون الداكن المعتاد --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white" style="border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">لوحة التحكم 🎓</h2>
            <p class="text-white-50 mb-0">أهلاً بك يا {{ Auth::user()->name }} 👋. نتمنى لك فصلاً دراسياً موفقاً.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-user-graduate fa-4x text-light opacity-25"></i>
        </div>
    </div>
</div>

{{-- 2. كروت الإحصائيات العلوية --}}
<div class="row g-4 mb-4">
    
    {{-- كرت المواد الدراسية (معدل للأسود) --}}
    <div class="col-md-3">
        <a href="{{ route('student.subjects.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-dark hover-shadow transition-all">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-dark text-uppercase mb-1">المواد الدراسية</div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $subjects->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book-open fa-2x text-dark opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- كرت الامتحانات القادمة --}}
    <div class="col-md-3">
        <a href="{{ route('student.exams.calendar') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-warning hover-shadow transition-all">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">الامتحانات القادمة</div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $totalUpcomingExams }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- كرت الفصل الدراسي --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">الفصل الدراسي</div>
                        <div class="h5 mb-0 fw-bold text-dark mt-2">{{ $class ? $class->name : 'غير مسكن' }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard fa-2x text-info opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- كرت الاختبارات التجريبية --}}
    <div class="col-md-3">
        <a href="{{ route('student.subjects.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-success hover-shadow transition-all">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">اختبارات الدروس</div>
                            <div class="h6 mb-0 fw-bold text-dark mt-2">مراجعة وتقييم</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-signature fa-2x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>

{{-- 3. القسم السفلي (الإجراءات، المواد، والتنبيهات) --}}
<div class="row">
    <div class="col-lg-8">
        {{-- كرت الإجراءات السريعة --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-bolt text-warning me-2"></i> إجراءات سريعة</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap">
                    
                    {{-- تم التعديل إلى الأسود --}}
                    <a href="{{ route('student.report_card') }}" class="btn btn-outline-dark btn-lg flex-grow-1 shadow-sm py-3">
                        <i class="fas fa-file-invoice mb-2 d-block fs-3"></i>
                        كشف الدرجات
                    </a>

                    <a href="{{ route('student.schedule') }}" class="btn btn-outline-success btn-lg flex-grow-1 shadow-sm py-3">
                        <i class="fas fa-calendar-week mb-2 d-block fs-3"></i>
                        الجدول الدراسي
                    </a>

                    <a href="{{ route('messages.index') }}" class="btn btn-outline-info btn-lg flex-grow-1 shadow-sm py-3">
                        <i class="fas fa-comments mb-2 d-block fs-3"></i>
                        تواصل مع المعلمين
                    </a>
                    
                </div>
            </div>
        </div>

        {{-- قائمة المواد --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-book-open text-dark me-2"></i> مقرراتي الدراسية</h6>
                <a href="{{ route('student.subjects.index') }}" class="btn btn-sm btn-light border">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($subjects as $subject)
                        <a href="{{ route('student.subjects.show', $subject->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">{{ $subject->name }}</h6>
                                <small class="text-muted"><i class="fas fa-user-tie me-1"></i> المعلم: {{ $subject->teacher_name ?? 'غير محدد' }}</small>
                            </div>
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 shadow-sm">
                                <i class="fas fa-eye me-1 text-dark"></i> عرض الدروس
                            </span>
                        </a>
                    @empty
                        <div class="list-group-item text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x opacity-25 mb-3 d-block"></i>
                            لا توجد مواد مسجلة حالياً.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- 4. كرت الامتحانات القادمة (جدول زمني جانبي) --}}
    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-secondary">📅 الامتحانات القادمة</h6>
                <a href="{{ route('student.exams.calendar') }}" class="btn btn-sm btn-light text-danger"><i class="fas fa-calendar-alt"></i> التقويم</a>
            </div>
            <div class="card-body py-4">
                @if($totalUpcomingExams > 0)
                    <div class="timeline">
                        @foreach($subjects as $subject)
                            @if(isset($subject->upcoming_exams))
                                @foreach($subject->upcoming_exams as $exam)
                                    {{-- توجيه الطالب لصفحة المادة لإجراء الامتحان --}}
                                    <a href="{{ route('student.subjects.show', $subject->id) }}" class="text-decoration-none d-block mb-3 pb-3 border-bottom hover-bg-light p-2 rounded transition-all">
                                        <div class="d-flex align-items-start">
                                            <div class="icon-circle bg-light text-danger me-3" style="width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #ffe5e5;">
                                                <span class="fw-bold">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d') }}</span>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1 text-dark">{{ $subject->name }}</h6>
                                                <small class="text-muted"><i class="fas fa-clock me-1"></i> {{ $exam->title ?? 'امتحان' }} - {{ \Carbon\Carbon::parse($exam->exam_date)->translatedFormat('l') }}</small>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center d-flex flex-column justify-content-center h-100 pb-5">
                        <i class="fas fa-check-circle fa-4x text-success opacity-25 mb-3"></i>
                        <h6 class="fw-bold text-dark">لا توجد امتحانات قريبة</h6>
                        <p class="text-muted small">راجع دروسك بانتظام وكن مستعداً.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* إضافة تأثير خفيف عند تمرير الماوس على الكروت القابلة للضغط */
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

@endsection