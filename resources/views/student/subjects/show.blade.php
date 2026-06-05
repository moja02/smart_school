@extends('layouts.student')
@section('content')

{{-- 1. ترويسة المادة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #1e3c72, #2a5298); border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">{{ $subject->name }} 📘</h2>
            <p class="text-white-50 mb-0">قائمة الدروس، المراجعات، والاختبارات المتاحة للمادة.</p>
        </div>
        <div>
            <a href="{{ route('student.subjects.index') }}" class="btn btn-light text-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-right me-2"></i> عودة للمواد
            </a>
        </div>
    </div>
</div>

{{-- 2. ملخص الدرجات --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
        <h5 class="m-0 fw-bold text-dark"><i class="fas fa-chart-pie text-warning me-2"></i> موقفي من الدرجات</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4 text-center">
            
            {{-- أعمال السنة --}}
            <div class="col-md-4 border-end-md">
                <p class="text-muted fw-bold mb-1"><i class="fas fa-tasks text-info me-1"></i> أعمال الفصل</p>
                <h3 class="fw-bold text-dark mb-2">{{ $worksTotal }} <span class="fs-6 text-muted">/ {{ $distribution['works'] }}</span></h3>
                <div class="progress" style="height: 8px;">
                    @php $worksPercent = $distribution['works'] > 0 ? ($worksTotal / $distribution['works']) * 100 : 0; @endphp
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $worksPercent }}%;"></div>
                </div>
            </div>

            {{-- النهائي --}}
            <div class="col-md-4 border-end-md">
                <p class="text-muted fw-bold mb-1"><i class="fas fa-file-signature text-danger me-1"></i> الامتحان النهائي</p>
                <h3 class="fw-bold text-dark mb-2">{{ $finalTotal }} <span class="fs-6 text-muted">/ {{ $distribution['final'] }}</span></h3>
                <div class="progress" style="height: 8px;">
                    @php $finalPercent = $distribution['final'] > 0 ? ($finalTotal / $distribution['final']) * 100 : 0; @endphp
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $finalPercent }}%;"></div>
                </div>
            </div>

            {{-- المجموع الكلي --}}
            <div class="col-md-4">
                <p class="text-muted fw-bold mb-1"><i class="fas fa-trophy text-success me-1"></i> المجموع الكلي</p>
                <h3 class="fw-bold text-{{ $studentTotal >= ($distribution['total']/2) ? 'success' : 'danger' }} mb-2">
                    {{ $studentTotal }} <span class="fs-6 text-muted">/ {{ $distribution['total'] }}</span>
                </h3>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;"></div>
                </div>
            </div>

        </div>

        {{-- سجل التقييمات التفصيلي (الجدول المضاف) --}}
        <div class="mt-5 pt-4 border-top">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-clipboard-list text-primary me-2"></i> التقييمات والواجبات المطلوبة:</h5>
            
            @if($assessments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle border shadow-sm rounded">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>التقييم / الاختبار</th>
                            <th>النوع</th>
                            <th class="text-center">الدرجة العظمى</th>
                            <th class="text-center">درجتك</th>
                            <th class="text-center">ملاحظات المعلم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                            @php
                                $title = $assessment->title ?? $assessment->name ?? 'تقييم';
                                $isFinal = str_contains(strtolower($title), 'final') || str_contains(strtolower($title), 'نهائي');
                                $typeLabel = $isFinal ? 'امتحان نهائي' : 'أعمال فصل';
                                $typeColor = $isFinal ? 'danger' : 'info';
                                $maxScore = $assessment->max_score ?? $assessment->full_mark ?? '--';
                            @endphp
                            <tr>
                                <td class="fw-bold text-dark">{{ $title }}</td>
                                <td><span class="badge bg-{{ $typeColor }} bg-opacity-10 text-{{ $typeColor }} px-3 py-1 rounded-pill">{{ $typeLabel }}</span></td>
                                <td class="text-center text-muted">{{ $maxScore }}</td>
                                <td class="text-center">
                                    @if($assessment->student_mark !== null)
                                        <span class="fw-bold text-success fs-5">{{ $assessment->student_mark }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1 shadow-sm">لم تُرصد بعد</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted small">{{ $assessment->student_notes ?: '--' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-light border text-center text-muted mb-0">
                <i class="fas fa-info-circle fa-2x mb-2 d-block opacity-50"></i>
                لم يقم المعلم بإضافة أي تقييمات لهذه المادة حتى الآن.
            </div>
            @endif
        </div>

    </div>
</div>

{{-- 3. قائمة الدروس والاختبارات --}}
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
        <h5 class="m-0 fw-bold text-dark"><i class="fas fa-list-ul text-primary me-2"></i> الدروس والمحتوى</h5>
    </div>
    <div class="card-body p-4">
        <div class="accordion accordion-flush" id="lessonsAccordion">
            @forelse($lessons as $lesson)
            <div class="accordion-item mb-3 border rounded shadow-sm overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $lesson->id }}">
                        <i class="fas fa-chalkboard-teacher text-primary me-3 fs-5"></i> 
                        <span class="fs-5">{{ $lesson->title }}</span>
                    </button>
                </h2>
                <div id="collapse{{ $lesson->id }}" class="accordion-collapse collapse" data-bs-parent="#lessonsAccordion">
                    <div class="accordion-body bg-light border-top">
                        
                        @if($lesson->questions->count() > 0)
                            <div class="text-center py-4">
                                @php
                                    $attempt = \App\Models\QuizAttempt::where('student_id', Auth::user()->studentProfile->id ?? Auth::id())
                                                ->where('lesson_id', $lesson->id)
                                                ->first();
                                @endphp

                                @if($attempt)
                                    <div class="alert alert-success d-inline-block px-5 py-3 shadow-sm border-0 rounded-4">
                                        <h5 class="fw-bold mb-2 text-success"><i class="fas fa-check-circle me-2"></i> تم أداء الاختبار</h5>
                                        <h3 class="mb-0 text-dark fw-bold my-3">درجتك: <span class="text-success">{{ $attempt->score }}</span> / {{ $attempt->total }}</h3>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill border">لا يمكن إعادة المحاولة</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <i class="fas fa-clipboard-list fa-4x text-warning mb-3"></i>
                                    </div>
                                    <h4 class="fw-bold mb-2">اختبار تقييمي للدرس</h4>
                                    <p class="text-muted mb-4 small"><i class="fas fa-exclamation-triangle text-danger me-1"></i> تنبيه: يمكنك إجراء الاختبار مرة واحدة فقط، سيتم إرسال النتيجة لمعلمك مباشرة.</p>
                                    
                                    <a href="{{ route('student.quiz.start', $lesson->id) }}" class="btn btn-warning text-dark btn-lg rounded-pill px-5 shadow fw-bold hover-scale">
                                        <i class="fas fa-play-circle me-2"></i> ابدأ الاختبار الآن
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="opacity-25 mb-3"><i class="fas fa-file-alt fa-3x text-muted"></i></div>
                                <h6 class="text-muted mb-0">لم يقم المعلم بإضافة أسئلة تدريبية لهذا الدرس بعد.</h6>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div class="opacity-25 mb-3"><i class="fas fa-box-open fa-4x text-muted"></i></div>
                <h5 class="fw-bold text-secondary">لا توجد دروس حالياً</h5>
                <p class="text-muted">لم يتم إضافة أي محتوى لهذه المادة حتى الآن.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .hover-scale:hover { transform: scale(1.05); transition: 0.2s ease-in-out; }
    .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: #1e3c72; box-shadow: none; }
    .accordion-button:focus { box-shadow: none; border-color: rgba(0,0,0,.125); }
    @media (min-width: 768px) { .border-end-md { border-left: 1px solid #dee2e6; } }
</style>
@endsection