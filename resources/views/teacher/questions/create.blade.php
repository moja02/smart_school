@extends('layouts.teacher')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container py-4">
    
    {{-- الهيدر --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-white mb-1">إضافة سؤال جديد</h2>
                <p class="mb-0 opacity-75">المادة: {{ $subject->name }} | الشعبة: {{ $class->section }}</p>
            </div>
            <a href="{{ route('teacher.quizzes.index', ['subject_id' => $subject->id, 'section_id' => $class->id]) }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="fas fa-arrow-right me-2"></i> العودة للاختبارات
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0 fw-bold">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        {{-- 1. نموذج الإضافة --}}
        <div class="col-lg-5">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 fw-bold text-primary border-bottom-0">
                    <i class="fas fa-plus-circle me-2"></i> بيانات السؤال
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.questions.store', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" method="POST">
                        @csrf
                        
                        {{-- اختيار الدرس --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">الدرس</label>
                            <div class="input-group">
                                <select name="lesson_id" class="form-select bg-light">
                                    <option value="">-- اختر درساً موجوداً --</option>
                                    @foreach($lessons as $l)
                                        <option value="{{ $l->id }}">{{ $l->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-center my-2 text-muted small">- أو -</div>
                            <input type="text" name="lesson_name" class="form-control" placeholder="اكتب اسم درس جديد...">
                        </div>

                        {{-- نص السؤال --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">نص السؤال</label>
                            <textarea name="content" class="form-control" rows="3" required placeholder="اكتب السؤال هنا..."></textarea>
                        </div>

                        {{-- نوع السؤال --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">نوع السؤال</label>
                            <select name="type" class="form-select" id="qType" onchange="toggleOptions()">
                                <option value="multiple_choice">اختيار من متعدد</option>
                                <option value="true_false">صح / خطأ</option>
                            </select>
                        </div>

                        {{-- الخيارات (تظهر فقط للاختيار من متعدد) --}}
                        <div id="opts" class="mb-4 bg-light p-3 rounded border">
                            <label class="form-label fw-bold small text-muted mb-2">الخيارات</label>
                            <div class="vstack gap-2">
                                <input type="text" name="options[]" class="form-control form-control-sm" placeholder="الخيار الأول">
                                <input type="text" name="options[]" class="form-control form-control-sm" placeholder="الخيار الثاني">
                                <input type="text" name="options[]" class="form-control form-control-sm" placeholder="الخيار الثالث">
                                <input type="text" name="options[]" class="form-control form-control-sm" placeholder="الخيار الرابع">
                            </div>
                        </div>

                        {{-- الإجابة الصحيحة --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">الإجابة الصحيحة</label>
                            <input type="text" name="correct_answer" class="form-control border-success" required placeholder="انسخ الإجابة الصحيحة هنا تماماً">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm hover-scale">
                            <i class="fas fa-save me-2"></i> حفظ السؤال
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. بنك الأسئلة المضافة سابقاً (الجزء الجديد) --}}
        <div class="col-lg-7">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-database me-2"></i> الأسئلة المضافة لهذا الفصل</h6>
                    <span class="badge bg-light text-dark border">{{ \DB::table('questions')->where('subject_id', $subject->id)->where('section_id', $class->id)->count() }} سؤال</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-3 text-start" width="50%">السؤال</th>
                                    <th width="20%">الدرس</th>
                                    <th width="15%">النوع</th>
                                    <th width="15%">الإجراءات</th> {{-- ✅ عمود جديد --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentQuestions = \DB::table('questions')
                                        ->leftJoin('lessons', 'questions.lesson_id', '=', 'lessons.id')
                                        ->where('questions.subject_id', $subject->id)
                                        ->where('questions.section_id', $class->id)
                                        ->select('questions.*', 'lessons.title as lesson_title')
                                        ->orderByDesc('questions.created_at')
                                        ->get()
                                        ->unique('content'); //  يمنع تكرار عرض نفس السؤال
                                @endphp

                                @forelse($recentQuestions as $q)
                                <tr>
                                    <td class="ps-3 text-start">
                                        <p class="mb-0 fw-bold text-dark small">{{ Str::limit($q->content, 40) }}</p>
                                        <small class="text-success">{{ $q->correct_answer }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $q->lesson_title ?? 'عام' }}</span></td>
                                    <td>
                                        @if($q->type == 'true_false')
                                            <span class="badge bg-secondary">صح/خطأ</span>
                                        @else
                                            <span class="badge bg-primary">اختيارات</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary shadow-sm me-1" 
                                            data-url="{{ route('teacher.questions.update', $q->id) }}"
                                            onclick="openEditModal(this, {{ json_encode($q) }})" 
                                            title="تعديل السؤال">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {{-- ✅ زر الحذف مع التأكيد --}}
                                        <button class="btn btn-sm btn-outline-danger shadow-sm" onclick="confirmDeleteQuestion({{ $q->id }})" title="حذف السؤال">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        
                                        {{-- نموذج الحذف المخفي --}}
                                        <form id="delete-question-{{ $q->id }}" action="{{ route('teacher.questions.destroy', $q->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2 opacity-25"></i>
                                        <p class="mb-0 small">لم يتم إضافة أي أسئلة بعد.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOptions(){ 
    let type = document.getElementById('qType').value;
    let opts = document.getElementById('opts');
    if(type == 'multiple_choice') {
        opts.style.display = 'block';
    } else {
        opts.style.display = 'none';
    }
}
// تشغيل الدالة عند التحميل لضبط الحالة الأولية
toggleOptions();
</script>
{{-- ✅ سكريبت التنبيه قبل الحذف (ضعه في نهاية الملف) --}}
<script>
    function confirmDeleteQuestion(id) {
        Swal.fire({
            title: 'حذف السؤال؟',
            text: "لن تتمكن من استرجاع هذا السؤال مرة أخرى!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-question-' + id).submit();
            }
        })
    }

</script>
<script>
   // لاحظ أننا أضفنا (button) كأول متغير للدالة
function openEditModal(button, question) {
    var form = document.getElementById('editForm');

    // 1. ✅ الحل الجذري: نأخذ الرابط الجاهز من الزر مباشرة
    var url = button.getAttribute('data-url');
    form.action = url;

    // للتحقق: اطبع الرابط في الكونسول لتره بعينك
    console.log("Rout to update:", url);

    // 2. تعبئة البيانات
    document.getElementById('edit_content').value = question.content;
    document.getElementById('edit_type').value = question.type;
    document.getElementById('edit_correct_answer').value = question.correct_answer;

    // 3. معالجة الخيارات
    try {
        let opts = (typeof question.options === 'string') ? JSON.parse(question.options) : question.options;
        if (!Array.isArray(opts)) opts = [];

        document.getElementById('opt1').value = opts[0] || '';
        document.getElementById('opt2').value = opts[1] || '';
        document.getElementById('opt3').value = opts[2] || '';
        document.getElementById('opt4').value = opts[3] || '';
    } catch (e) {
        document.getElementById('opt1').value = ''; 
        document.getElementById('opt2').value = '';
        document.getElementById('opt3').value = '';
        document.getElementById('opt4').value = '';
    }

    // 4. ضبط العرض وفتح النافذة
    toggleEditOptions();
    var myModal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
    myModal.show();
}

function toggleEditOptions() {
    let type = document.getElementById('edit_type').value;
    let div = document.getElementById('edit_opts_div');
    div.style.display = (type === 'multiple_choice') ? 'block' : 'none';
}
</script>
<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-2px); }
</style>

{{-- نافذة تعديل السؤال (Modal) --}}
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> تعديل السؤال</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    {{-- نص السؤال --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">نص السؤال</label>
                        <textarea name="content" id="edit_content" class="form-control" rows="3" required></textarea>
                    </div>

                    {{-- النوع --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع السؤال</label>
                        <select name="type" id="edit_type" class="form-select" onchange="toggleEditOptions()">
                            <option value="multiple_choice">اختيار من متعدد</option>
                            <option value="true_false">صح / خطأ</option>
                        </select>
                    </div>

                    {{-- الخيارات (للاختيار من متعدد) --}}
                    <div id="edit_opts_div" class="mb-3 bg-light p-3 rounded border">
                        <label class="form-label fw-bold mb-2">الخيارات</label>
                        <input type="text" name="options[]" id="opt1" class="form-control form-control-sm mb-2" placeholder="الخيار 1">
                        <input type="text" name="options[]" id="opt2" class="form-control form-control-sm mb-2" placeholder="الخيار 2">
                        <input type="text" name="options[]" id="opt3" class="form-control form-control-sm mb-2" placeholder="الخيار 3">
                        <input type="text" name="options[]" id="opt4" class="form-control form-control-sm mb-2" placeholder="الخيار 4">
                    </div>

                    {{-- الإجابة الصحيحة --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">الإجابة الصحيحة</label>
                        <input type="text" name="correct_answer" id="edit_correct_answer" class="form-control border-success" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection