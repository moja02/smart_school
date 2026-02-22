@extends('layouts.student')

@section('content')

@php
    $totalUpcomingExams = $subjects->sum(function($subject) {
        return $subject->upcoming_exams->count();
    });
@endphp

{{-- 1. الترويسة (نفس ستايل المدير) --}}
<div class="card page-header-card mb-4 shadow border-0">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">لوحة التحكم 🎓</h2>
            <p class="text-white-50 mb-0">أهلاً بك يا {{ Auth::user()->name }} 👋. نتمنى لك فصلاً دراسياً موفقاً.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-user-graduate fa-4x text-primary opacity-25"></i>
        </div>
    </div>
</div>

{{-- 2. كروت الإحصائيات العلوية (4 كروت بنفس تنسيق الإدارة) --}}
<div class="row g-4 mb-4">
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">المواد الدراسية</div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ $subjects->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-open fa-2x text-gray-300 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">الامتحانات القادمة</div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ $totalUpcomingExams }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">الفصل الدراسي</div>
                        <div class="h5 mb-0 fw-bold text-dark mt-2">{{ $class ? $class->name : 'غير مسكن' }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard fa-2x text-gray-300 text-info opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. كرت الاختبارات التجريبية --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">اختبارات تجريبية</div>
                        <div class="h6 mb-0 fw-bold text-dark mt-2">من الأستاذ</div>
                    </div>
                    <div class="col-auto">
                        {{-- حطينا علامة # مؤقتاً لين نبرمجو صفحة الاختبارات --}}
                        <a href="#" class="text-decoration-none">
                            <i class="fas fa-file-signature fa-2x text-gray-300 text-success opacity-25"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- 3. القسم السفلي (الإجراءات، المواد، والتنبيهات) --}}
<div class="row">
    <div class="col-lg-8">
        {{-- كرت الإجراءات السريعة (مربع الأزرار الثلاثة) --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-bolt text-warning me-2"></i> إجراءات سريعة</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap">
                    
                    <a href="{{ route('student.report_card') }}" class="btn btn-outline-primary btn-lg flex-grow-1 shadow-sm py-3">
                        <i class="fas fa-file-invoice mb-2 d-block fs-3"></i>
                        كشف الدرجات
                    </a>

                    <a href="#" class="btn btn-outline-success btn-lg flex-grow-1 shadow-sm py-3">
                        <i class="fas fa-calendar-week mb-2 d-block fs-3"></i>
                        الجدول الدراسي
                    </a>

                    <a href="{{ route('messages.index') }}" class="btn btn-outline-info btn-lg flex-grow-1 shadow-sm py-3">
                        <i class="fas fa-chalkboard-teacher mb-2 d-block fs-3"></i>
                        تواصل مع المعلمين
                    </a>
                    
                </div>
            </div>
        </div>

        {{-- قائمة المواد --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-book-open text-primary me-2"></i> مقرراتي الدراسية</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($subjects as $subject)
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">{{ $subject->name }}</h6>
                                <small class="text-muted"><i class="fas fa-user-tie me-1"></i> المعلم: {{ $subject->teacher_name }}</small>
                            </div>
                            <span class="badge bg-light text-primary border rounded-pill px-3 py-2">
                                <i class="fas fa-star me-1 text-warning"></i> {{ $subject->my_grades->count() }} درجات مسجلة
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">لا توجد مواد مسجلة حالياً.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- 4. كرت الامتحانات (بديل لكود "حالة النظام" في صفحة الإدارة) --}}
    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary">📅 الامتحانات القادمة</h6>
            </div>
            <div class="card-body py-4">
                @if($totalUpcomingExams > 0)
                    <div class="timeline">
                        @foreach($subjects as $subject)
                            @foreach($subject->upcoming_exams as $exam)
                                <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                    <div class="icon-circle bg-light text-danger me-3" style="width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #ffe5e5;">
                                        <span class="fw-bold">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d') }}</span>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">{{ $subject->name }}</h6>
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i> {{ $exam->title ?? 'امتحان' }} - {{ \Carbon\Carbon::parse($exam->exam_date)->translatedFormat('l') }}</small>
                                    </div>
                                </div>
                            @endforeach
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

@endsection