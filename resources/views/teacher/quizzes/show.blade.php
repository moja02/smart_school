@extends('layouts.teacher')

@section('content')
<div class="container py-4">

    {{-- الهيدر --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white d-print-none">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-white mb-1">تفاصيل الاختبار</h2>
                <p class="mb-0 opacity-75">
                    {{ $quiz->title }} | {{ $quiz->subject_name }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('teacher.quizzes.report', $quiz->id) }}" target="_blank" class="btn btn-info rounded-pill px-3 fw-bold text-white shadow-sm">
                    <i class="fas fa-file-pdf me-2"></i> نسخة الطباعة (PDF)
                </a>
                <a href="{{ route('teacher.quizzes.index', ['subject_id' => $quiz->subject_id, 'section_id' => $quiz->section_id]) }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="fas fa-arrow-right me-2"></i> عودة
                </a>
            </div>
        </div>
    </div>

    {{-- ورقة الاختبار (تصميم يحاكي الورقة الحقيقية) --}}
    <div class="card shadow-lg border-0" id="examPaper">
        <div class="card-body p-5">
            
            {{-- ترويسة الورقة --}}
            <div class="row border-bottom pb-4 mb-4 align-items-center text-center text-md-start">
                <div class="col-md-4 text-center text-md-end">
                    <h5 class="fw-bold mb-1">المادة: {{ $quiz->subject_name }}</h5>
                    <p class="text-muted mb-0">الشعبة: {{ $quiz->section_name }}</p>
                </div>
                <div class="col-md-4 text-center">
                    <h3 class="fw-bold text-decoration-underline">{{ $quiz->title }}</h3>
                    <p class="text-muted small mb-0">{{ $quiz->description }}</p>
                </div>
                <div class="col-md-4 text-center text-md-start mt-3 mt-md-0">
                    <h5 class="fw-bold mb-1">الزمن: {{ $quiz->duration }} دقيقة</h5>
                    <p class="text-muted mb-0">عدد الأسئلة: {{ count($questions) }}</p>
                </div>
            </div>

            {{-- الأسئلة --}}
            @forelse($questions as $index => $q)
                <div class="question-box mb-4 p-3 rounded {{ $loop->even ? 'bg-light' : '' }}">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            <span class="badge bg-dark rounded-circle p-2 fs-6" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-dark mb-3">{{ $q->content }}</h5>

                            @if($q->type == 'true_false')
                                {{-- خيارات صح وخطأ --}}
                                <div class="d-flex gap-4 ms-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" disabled>
                                        <label class="form-check-label">صح</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" disabled>
                                        <label class="form-check-label">خطأ</label>
                                    </div>
                                </div>
                            
                            @elseif($q->type == 'multiple_choice' && !empty($q->options))
                                {{-- خيارات متعددة --}}
                                @php $options = json_decode($q->options); @endphp
                                <div class="row g-2">
                                    @foreach($options as $opt)
                                        <div class="col-md-6">
                                            <div class="border rounded p-2 text-muted bg-white">
                                                <i class="far fa-square me-2"></i> {{ $opt }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            {{-- الإجابة الصحيحة (تظهر فقط للمعلم في الشاشة، وتختفي عند الطباعة) --}}
                            <div class="mt-2 d-print-none">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                    <i class="fas fa-check me-1"></i> الإجابة: {{ $q->correct_answer }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>لا توجد أسئلة في هذا الاختبار.</p>
                </div>
            @endforelse

            {{-- تذييل الورقة --}}
            <div class="mt-5 pt-4 border-top text-center text-muted small">
                <p class="mb-0">انتهت الأسئلة - مع تمنياتنا بالتوفيق</p>
                <p>تم استخراج هذا الاختبار بواسطة نظام المدرسة الذكية</p>
            </div>
        </div>
    </div>

</div>

{{-- تنسيقات الطباعة --}}
<style>
    @media print {
        body * { visibility: hidden; }
        #examPaper, #examPaper * { visibility: visible; }
        #examPaper { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
        .d-print-none { display: none !important; }
        .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection