@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'تمت العملية بنجاح!', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000, toast: true, position: 'top-end' });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error', title: 'تنبيه!', text: "{{ session('error') }}", confirmButtonColor: '#d33', confirmButtonText: 'حسناً فهمت' });
        </script>
    @endif

    {{-- الترويسة الرئيسية --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">إدارة التقييمات</h2>
                    <span class="badge bg-warning text-dark">{{ $subject->name }}</span>
                </div>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $section->section ?? 'غير محدد' }}) | 
                    <i class="fas fa-calculator me-1"></i> الحد الأقصى لكل فصل دراسي: {{ $maxPerSemester ?? 0 }} درجة
                </p>
            </div>
            <div class="d-flex align-items-center gap-4">
                <a href="{{ route('teacher.class.show', [$subject->id, $section->id]) }}" class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصل
                </a>
            </div>
        </div>
    </div>

    @if(!empty($isLocked) && $isLocked)
        <div class="alert alert-danger shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
            <div class="d-flex align-items-center">
                <i class="fas fa-lock fa-2x me-3"></i>
                <div>
                    <h5 class="fw-bold mb-1">نظام الرصد مغلق</h5>
                    <p class="mb-0">لا يمكنك إضافة أو تعديل التقييمات بأمر من الإدارة.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- نموذج إضافة تقييم جديد --}}
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i> إضافة تقييم جديد</h6>
                </div>
                <div class="card-body">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger p-2 small">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teacher.assessments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <input type="hidden" name="section_id" value="{{ $section->id }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">اسم التقييم</label>
                            <input type="text" name="name" class="form-control" placeholder="مثلاً: اختبار قصير 1" required {{ (!empty($isLocked) && $isLocked) ? 'disabled' : '' }}>
                        </div>

                        {{-- اختيار السميستر (مربوط بالجافاسكريبت) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">الفصل الدراسي</label>
                            <select name="semester" id="semesterSelect" class="form-select" required {{ (!empty($isLocked) && $isLocked) ? 'disabled' : '' }}>
                                <option value="" disabled selected>-- حدد الفصل الدراسي --</option>
                                <option value="1" data-rem="{{ $remSem1 ?? 0 }}">الأول (المتبقي: {{ $remSem1 ?? 0 }})</option>
                                <option value="2" data-rem="{{ $remSem2 ?? 0 }}">الثاني (المتبقي: {{ $remSem2 ?? 0 }})</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">الدرجة العظمى</label>
                            <div class="input-group">
                                <input type="number" name="max_score" id="maxScoreInput" step="0.5" min="0.5" class="form-control fw-bold" placeholder="0" required disabled>
                                <span class="input-group-text bg-light text-muted">درجة</span>
                            </div>
                            <small class="text-muted d-block mt-2" id="scoreWarning">
                                <i class="fas fa-info-circle me-1"></i> يرجى تحديد الفصل الدراسي أولاً.
                            </small>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" disabled>
                            <i class="fas fa-save me-2"></i> إنشاء التقييم
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- جدول التقييمات الحالية --}}
        <div class="col-md-8">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-list-ul me-2"></i> التقييمات الحالية</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>اسم التقييم</th>
                                    <th>الدرجة العظمى</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($assessments as $assessment)
                                <tr>
                                    <td class="fw-bold text-dark text-start ps-4">
                                        {{ $assessment->name ?? $assessment->title }}
                                        <br>
                                        <span class="badge {{ $assessment->semester == 2 ? 'bg-info' : 'bg-secondary' }} text-white mt-1" style="font-size: 0.7rem;">
                                            {{ $assessment->semester == 2 ? 'السميستر الثاني' : 'السميستر الأول' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                            {{ $assessment->max_score }} درجة
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group shadow-sm" role="group">
                                            <a href="{{ route('teacher.assessments.marks', $assessment->id) }}" class="btn btn-sm btn-outline-primary" title="رصد الدرجات للطلاب">
                                                <i class="fas fa-clipboard-list"></i>
                                            </a>

                                            @if(empty($isLocked) || !$isLocked)
                                                <button type="button" class="btn btn-sm btn-outline-dark btn-edit-assessment" 
                                                    data-id="{{ $assessment->id }}"
                                                    data-name="{{ $assessment->name ?? $assessment->title }}"
                                                    data-score="{{ $assessment->max_score }}"
                                                    data-url="{{ route('teacher.assessments.update', $assessment->id) }}"
                                                    title="تعديل اسم أو درجة التقييم">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <form action="{{ route('teacher.assessments.destroy', $assessment->id) }}" method="POST" class="d-inline form-delete">
                                                    @csrf 
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-assessment" title="حذف التقييم نهائياً">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3"></i>
                                        <p class="text-muted mb-0">لم يتم إضافة أي تقييمات لهذا الفصل بعد.</p>
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

{{-- 🌑 نافذة التعديل (Modal) --}}
<div class="modal fade" id="editAssessmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-warning"></i>تعديل التقييم</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAssessmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3 bg-white p-3 rounded shadow-sm border">
                        <label class="fw-bold small mb-2 text-dark">اسم التقييم</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3 bg-white p-3 rounded shadow-sm border">
                        <label class="fw-bold small mb-2 text-dark">الدرجة العظمى الجديدة</label>
                        <div class="input-group">
                            <input type="number" name="max_score" id="edit_score" step="0.5" min="0.5" class="form-control" required>
                            <span class="input-group-text bg-light">درجة</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top">
                    <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 💡 الجافاسكريبت للتفاعل الذكي --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isLocked = {{ (!empty($isLocked) && $isLocked) ? 'true' : 'false' }};
        
        // 1. التفاعل مع اختيار السميستر والتحقق من الدرجة المسموحة
        const semesterSelect = document.getElementById('semesterSelect');
        const maxScoreInput = document.getElementById('maxScoreInput');
        const submitBtn = document.getElementById('submitBtn');
        const scoreWarning = document.getElementById('scoreWarning');

        function validateScore() {
            if (isLocked) return;
            const selectedOption = semesterSelect.options[semesterSelect.selectedIndex];
            if (!selectedOption.value) return;

            const maxAllowed = parseFloat(selectedOption.getAttribute('data-rem')) || 0;
            const enteredScore = parseFloat(maxScoreInput.value) || 0;

            if (enteredScore > maxAllowed) {
                scoreWarning.innerHTML = `<span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> تجاوزت الحد! أقصى ما يمكنك إضافته لهذا الفصل هو ${maxAllowed}</span>`;
                submitBtn.disabled = true;
                maxScoreInput.classList.add('border-danger');
            } else if (enteredScore > 0) {
                scoreWarning.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> ممتاز! سيتبقى لك في هذا الفصل: ${maxAllowed - enteredScore}</span>`;
                submitBtn.disabled = false;
                maxScoreInput.classList.remove('border-danger');
            } else {
                scoreWarning.innerHTML = `<i class="fas fa-info-circle me-1"></i> المتبقي لهذا الفصل: <span class="fw-bold text-dark">${maxAllowed}</span>`;
                submitBtn.disabled = true;
                maxScoreInput.classList.remove('border-danger');
            }
        }

        if (semesterSelect) {
            semesterSelect.addEventListener('change', function() {
                if (isLocked) return;
                const selectedOption = this.options[this.selectedIndex];
                const maxAllowed = parseFloat(selectedOption.getAttribute('data-rem')) || 0;

                if (maxAllowed <= 0) {
                    maxScoreInput.disabled = true;
                    maxScoreInput.value = '';
                    scoreWarning.innerHTML = `<span class="text-danger fw-bold"><i class="fas fa-times-circle me-1"></i> لقد استنفدت رصيد هذا الفصل بالكامل!</span>`;
                    submitBtn.disabled = true;
                } else {
                    maxScoreInput.disabled = false;
                    maxScoreInput.setAttribute('max', maxAllowed);
                    validateScore();
                }
            });
        }

        if (maxScoreInput) {
            maxScoreInput.addEventListener('input', validateScore);
        }

        // 2. إدارة التعديل
        const editModal = new bootstrap.Modal(document.getElementById('editAssessmentModal'));
        const editForm = document.getElementById('editAssessmentForm');

        document.querySelectorAll('.btn-edit-assessment').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_name').value = this.getAttribute('data-name');
                document.getElementById('edit_score').value = this.getAttribute('data-score');
                editForm.action = this.getAttribute('data-url');
                editModal.show();
            });
        });

        // 3. إدارة الحذف
        document.querySelectorAll('.btn-delete-assessment').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.form-delete');
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "سيتم حذف التقييم وجميع درجات الطلاب المرصودة به نهائياً!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#212529',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'تراجع'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    });
</script>
@endsection