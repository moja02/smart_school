@extends('layouts.teacher')
@section('title', 'إدارة أسئلة الاختبار')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3><i class="fas fa-list-ol text-primary me-2"></i> أسئلة الاختبار: {{ $exam->title }}</h3>
            <span class="badge bg-secondary">المادة: {{ $exam->subject->name }} | الفصل: {{ $exam->class->name }}</span>
        </div>
        <div>
            <a href="{{ route('teacher.online_exams.index') }}" class="btn btn-outline-secondary">رجوع</a>
            <button class="btn btn-success fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                <i class="fas fa-plus"></i> إضافة سؤال جديد
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($exam->questions->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($exam->questions as $index => $q)
                    <li class="list-group-item p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold mb-3"><span class="text-primary me-2">{{ $index + 1 }}.</span> {{ $q->question_text }} <span class="badge bg-info text-dark ms-2">{{ $q->score }} درجات</span></h5>
                        </div>
                        <div class="row">
                            @foreach($q->options as $opt)
                                <div class="col-md-6 mb-2">
                                    <div class="p-2 border rounded {{ $q->correct_answer == $opt ? 'bg-success text-white border-success' : 'bg-light' }}">
                                        @if($q->correct_answer == $opt) <i class="fas fa-check-circle me-2"></i> @else <i class="far fa-circle me-2 text-muted"></i> @endif
                                        {{ $opt }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center p-5 text-muted">
                    <i class="fas fa-question-circle fa-3x mb-3"></i>
                    <h5>لا توجد أسئلة حتى الآن</h5>
                    <p>ابدأ بإضافة الأسئلة ليتمكن الطلاب من حلها.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Add Question -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">إضافة سؤال خيارات متعددة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teacher.online_exams.questions.store', $exam->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">نص السؤال</label>
                        <textarea name="question_text" class="form-control form-control-lg" rows="2" required placeholder="اكتب السؤال هنا..."></textarea>
                    </div>
                    
                    <h6 class="fw-bold border-bottom pb-2 mb-3">الخيارات المتاحة (اكتب 2 على الأقل)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <input type="text" name="option_1" class="form-control" placeholder="الخيار الأول (إلزامي)" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="option_2" class="form-control" placeholder="الخيار الثاني (إلزامي)" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="option_3" class="form-control" placeholder="الخيار الثالث (اختياري)">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="option_4" class="form-control" placeholder="الخيار الرابع (اختياري)">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-success"><i class="fas fa-check-circle"></i> الإجابة الصحيحة بالضبط</label>
                            <input type="text" name="correct_answer" class="form-control" placeholder="يجب أن تتطابق حرفياً مع أحد الخيارات" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">درجة السؤال</label>
                            <input type="number" name="score" class="form-control" value="1" min="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-success px-4">إضافة السؤال</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
