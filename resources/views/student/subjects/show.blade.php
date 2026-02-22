@extends('layouts.student')
@section('content')

{{-- ترويسة المادة --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-primary">{{ $subject->name }}</h3>
        <p class="text-muted">الدروس والاختبارات</p>
    </div>
    <a href="{{ route('student.subjects.index') }}" class="btn btn-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-right me-1"></i> عودة
    </a>
</div>

{{-- قائمة الدروس --}}
<div class="accordion shadow-sm" id="lessonsAccordion">
    @forelse($lessons as $lesson)
    <div class="accordion-item mb-2 border-0 rounded overflow-hidden">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $lesson->id }}">
                <i class="fas fa-book-reader text-success me-2"></i> {{ $lesson->title }}
            </button>
        </h2>
        <div id="collapse{{ $lesson->id }}" class="accordion-collapse collapse" data-bs-parent="#lessonsAccordion">
            <div class="accordion-body bg-light">
                
                @if($lesson->questions->count() > 0)
                    <div class="text-center py-4">
                        @php
                            // التحقق هل الطالب امتحن هذا الدرس أم لا
                            // نستخدم \App\Models\QuizAttempt مباشرة داخل الـ View
                            $attempt = \App\Models\QuizAttempt::where('student_id', Auth::user()->studentProfile->id)
                                        ->where('lesson_id', $lesson->id)
                                        ->first();
                        @endphp

                        @if($attempt)
                            {{-- حالة: تم الاختبار مسبقاً --}}
                            <div class="alert alert-info d-inline-block px-5 py-3 shadow-sm border-0">
                                <h5 class="fw-bold mb-2"><i class="fas fa-check-circle text-success me-2"></i> تم أداء الاختبار</h5>
                                <h4 class="mb-0 text-primary fw-bold">درجتك: {{ $attempt->score }} / {{ $attempt->total }}</h4>
                                <div class="mt-2">
                                    <span class="badge bg-secondary">لا يمكن إعادة المحاولة</span>
                                </div>
                            </div>
                        @else
                            {{-- حالة: لم يختبر بعد --}}
                            <div class="mb-3">
                                <i class="fas fa-clipboard-check fa-3x text-primary opacity-50"></i>
                            </div>
                            <h5 class="fw-bold mb-3">اختبار مادة (درجات معتمدة)</h5>
                            <p class="text-muted mb-4 text-danger fw-bold small">⚠️ تنبيه: يمكنك إجراء هذا الاختبار مرة واحدة فقط وسيتم رصد الدرجة للمعلم.</p>
                            
                            <a href="{{ route('student.quiz.start', $lesson->id) }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm hover-scale">
                                <i class="fas fa-play me-2"></i> ابدأ الاختبار الآن
                            </a>
                        @endif
                    </div>
                @else
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i> لم يقم المعلم بإضافة أسئلة لهذا الدرس بعد.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <div class="opacity-50 mb-3"><i class="fas fa-folder-open fa-3x"></i></div>
        <p class="text-muted">لا توجد دروس مضافة لهذه المادة حالياً.</p>
    </div>
    @endforelse
</div>

<style>
    .hover-scale:hover { transform: scale(1.02); transition: 0.2s; }
</style>
@endsection