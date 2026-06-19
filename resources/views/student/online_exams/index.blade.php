@extends('layouts.student')
@section('title', 'الاختبارات الإلكترونية')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-laptop-code text-primary me-2"></i> الاختبارات الإلكترونية المتاحة</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="row g-4">
        @forelse($exams as $exam)
        <div class="col-md-6">
            <div class="card shadow border-0 h-100 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="fw-bold mb-0 text-dark">{{ $exam->title }}</h4>
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><i class="fas fa-clock"></i> {{ $exam->duration_minutes }} دقيقة</span>
                    </div>
                    
                    <p class="text-muted">{{ $exam->description ?? 'لا توجد تعليمات إضافية لهذا الاختبار.' }}</p>
                    
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-light text-dark border"><i class="fas fa-book text-success"></i> المادة: {{ $exam->subject->name }}</span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-chalkboard-teacher text-info"></i> المعلم: {{ $exam->teacher->name ?? 'غير محدد' }}</span>
                    </div>

                    @if($exam->my_attempt)
                        <div class="alert alert-success mb-0 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-check-circle"></i> لقد قمت بتسليم هذا الاختبار.</span>
                            <span class="fw-bold fs-5 border border-success rounded px-2 py-1 bg-white text-success">
                                {{ collect($exam->my_attempt->student_answers)->sum('is_correct') }} / {{ count($exam->my_attempt->student_answers) }}
                            </span>
                        </div>
                    @else
                        <a href="{{ route('student.online_exams.take', $exam->id) }}" class="btn btn-primary w-100 py-2 fw-bold fs-5 rounded-3">
                            <i class="fas fa-play-circle me-2"></i> ابدأ الاختبار الآن
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-light border text-center py-5 rounded-4 shadow-sm">
                <i class="fas fa-mug-hot fa-4x text-muted mb-3 opacity-50"></i>
                <h4 class="text-secondary fw-bold">لا توجد اختبارات إلكترونية حالياً</h4>
                <p class="text-muted mb-0">استرح قليلاً، لا يوجد أي امتحانات مبرمجة لفصلك في الوقت الحالي.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
