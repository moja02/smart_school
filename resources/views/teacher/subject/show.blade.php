@extends('layouts.teacher')

@section('content')

{{-- 1. ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(to right, #4e73df, #224abe); color: white;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="fas fa-book-open me-2"></i> إدارة مادة: {{ $subject->name }}</h3>
                <p class="mb-0 opacity-75">الفصل الدراسي: <strong>{{ $class->name }}</strong></p>
            </div>
            <div>
                <a href="{{ route('teacher.dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 text-primary fw-bold">
                    <i class="fas fa-arrow-right me-1"></i> عودة للرئيسية
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    {{-- بطاقة 1: بنك الأسئلة (إضافة محتوى) --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0 hover-scale text-center p-4">
            <div class="mb-3">
                <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-plus-circle fa-3x"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark">إضافة محتوى</h5>
            <p class="text-muted small">إضافة دروس جديدة وأسئلة للمنهج</p>
            {{-- زر المودال --}}
            <button class="btn btn-sm btn-outline-primary rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#addLessonModal">
                <i class="fas fa-plus me-1"></i> درس جديد
            </button>
        </div>
    </div>

    {{-- بطاقة 2: التقييمات (الاختبارات والواجبات ورصد الدرجات) --}}
    <div class="col-md-4">
        <a href="{{ route('teacher.assessments.index', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 hover-scale text-center p-4">
                <div class="mb-3">
                    <div class="avatar-lg bg-success bg-opacity-10 text-success rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-tasks fa-3x"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-dark">التقييمات والاختبارات</h5>
                <p class="text-muted small">إنشاء الاختبارات، الواجبات، ورصد الدرجات</p>
            </div>
        </a>
    </div>

    {{-- بطاقة 3: سجل الدرجات (التقارير) --}}
    <div class="col-md-4">
        <a href="{{ route('teacher.subject.report', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 hover-scale text-center p-4">
                <div class="mb-3">
                    <div class="avatar-lg bg-danger bg-opacity-10 text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-chart-bar fa-3x"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-dark">سجل الدرجات والتقارير</h5>
                <p class="text-muted small">عرض تقرير شامل لدرجات الطلاب</p>
            </div>
        </a>
    </div>
</div>

{{-- 2. قائمة الدروس والأسئلة (مع أزرار التعديل) --}}
<div class="card shadow border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group text-primary me-2"></i> محتوى المادة (الدروس والأسئلة)</h5>
    </div>
    <div class="card-body p-0">
        <div class="accordion accordion-flush" id="lessonsAccordion">
            @forelse($lessons as $lesson)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $lesson->id }}">
                    <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $lesson->id }}">
                        <span class="fw-bold text-dark">{{ $lesson->title }}</span>
                        <span class="badge bg-primary ms-3">{{ $lesson->questions->count() }} سؤال</span>
                    </button>
                </h2>
                <div id="collapse{{ $lesson->id }}" class="accordion-collapse collapse" data-bs-parent="#lessonsAccordion">
                    <div class="accordion-body">
                        
                        {{-- أدوات الدرس --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                            <span class="text-muted small">خيارات الدرس:</span>
                            <div>
                                <a href="{{ route('teacher.lesson.edit', $lesson->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit me-1"></i> تعديل الاسم
                                </a>
                                <a href="{{ route('teacher.questions.create', ['subject_id' => $subject->id, 'class_id' => $class->id, 'lesson_id' => $lesson->id]) }}" class="btn btn-sm btn-primary ms-1">
                                    <i class="fas fa-plus-circle me-1"></i> إضافة سؤال
                                </a>
                            </div>
                        </div>

                        {{-- قائمة الأسئلة --}}
                        @if($lesson->questions->count() > 0)
                            <div class="list-group">
                                @foreach($lesson->questions as $q)
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-dark">س: {{ $q->content }}</span>
                                        <br>
                                        <small class="text-muted text-success">
                                            <i class="fas fa-check-circle me-1"></i> الإجابة: {{ $q->correct_answer }}
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ route('teacher.question.edit', $q->id) }}" class="btn btn-sm btn-light text-secondary border hover-shadow" title="تعديل السؤال">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-2">لا توجد أسئلة في هذا الدرس.</p>
                        @endif

                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted">لم يتم إضافة أي دروس لهذه المادة بعد.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- مودال إضافة درس جديد --}}
<div class="modal fade" id="addLessonModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('teacher.lessons.store') }}" method="POST">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">إضافة درس جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">عنوان الدرس</label>
                        <input type="text" name="title" class="form-control" required placeholder="مثال: الدرس الأول: المقدمة">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ الدرس</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); cursor: pointer; }
    .hover-shadow:hover { background-color: #f8f9fa; border-color: #ccc !important; }
</style>
@endsection