@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow bg-dark text-white" style="border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1 text-white">المواد الدراسية (توزيع الحصص) 📚</h3>
            <p class="mb-0 text-white-50">إدارة المواد العامة والخاصة وتوزيع الحصص.</p>
        </div>
        <div class="d-flex gap-2">
            {{-- زر تعديل الهيكلية --}}
            <a href="{{ route('admin.settings.structure') }}" class="btn btn-outline-light shadow-sm">
                <i class="fas fa-cogs me-1"></i> إعداد المراحل
            </a>
            
            {{-- زر إضافة مادة --}}
            <button class="btn btn-light text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                <i class="fas fa-plus me-1"></i> إضافة مادة جديدة
            </button>
        </div>
    </div>
</div>

{{-- عرض رسائل النجاح والفشل --}}
@if(session('error'))
    <div class="alert alert-danger mb-4 shadow-sm border-0 border-start border-4 border-danger">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
    </div>
@endif

{{-- تجميع الصفوف حسب المرحلة ديناميكياً --}}
@php
    $groupedGrades = $grades->groupBy('stage');
    
    $stageNames = [
        'primary' => 'المرحلة الابتدائية',
        'middle' => 'المرحلة الإعدادية',
        'secondary' => 'المرحلة الثانوية'
    ];
@endphp

@forelse($groupedGrades as $stageKey => $stageGrades)
    <div class="mb-5">
        <h4 class="fw-bold text-dark border-bottom pb-2 mb-3">
            <i class="fas fa-layer-group text-muted me-2"></i> {{ $stageNames[$stageKey] ?? $stageKey }}
        </h4>
        
        <div class="row">
            @foreach($stageGrades as $grade)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
                    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center" style="border-radius: 1rem 1rem 0 0;">
                        <h6 class="m-0 fw-bold">{{ $grade->name }}</h6>
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                            {{ $grade->subjects->sum(fn($sub) => $sub->getClassesCount()) }} حصة
                        </span>
                    </div>
                    
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4 w-50">المادة</th>
                                    <th class="text-center w-25">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grade->subjects as $subject)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">{{ $subject->name }}</span>
                                        @if($subject->school_id != null)
                                            <span class="badge bg-dark bg-opacity-10 text-dark small ms-2 px-2 py-1"><i class="fas fa-school me-1"></i> مادة خاصة</span>
                                        @endif
                                    </td>
                                    
                                    {{-- عمود الإجراءات --}}
                                    <td class="text-center">
                                        @if($subject->school_id != null)
                                            <div class="btn-group" role="group">
                                                {{-- زر التعديل --}}
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-dark btn-edit-subject" 
                                                        data-id="{{ $subject->id }}"
                                                        data-name="{{ $subject->name }}"
                                                        data-classes="{{ $subject->weekly_classes }}"
                                                        data-grade="{{ $subject->grade_id }}"
                                                        title="تعديل البيانات">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                {{-- زر الحذف --}}
                                                <form action="{{ route('admin.subjects.delete', $subject->id) }}" method="POST" class="d-inline form-delete">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="حذف">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted border"><i class="fas fa-lock me-1"></i> أساسية</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small"><i class="fas fa-info-circle mb-2 fa-2x opacity-25 d-block"></i> لا توجد مواد مضافة.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="text-center py-5 bg-white shadow-sm" style="border-radius: 1rem;">
        <div class="mb-3 opacity-25"><i class="fas fa-layer-group fa-4x text-dark"></i></div>
        <h4 class="fw-bold text-dark">لم يتم تحديد المراحل الدراسية بعد!</h4>
        <p class="text-muted">يرجى الذهاب لإعدادات الهيكلية وتحديد المراحل التي تدرسها مدرستك لتتمكن من إضافة المواد.</p>
        <a href="{{ route('admin.settings.structure') }}" class="btn btn-dark px-4 py-2 mt-2 fw-bold">إعداد المراحل الآن</a>
    </div>
@endforelse

{{-- ========================== --}}
{{-- 🟢 نافذة إضافة مادة (Modal - باللون الداكن و Checkboxes) --}}
{{-- ========================== --}}
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-warning"></i> إضافة مادة خاصة جديدة</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger p-3 small rounded border-0 shadow-sm">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li><i class="fas fa-exclamation-circle me-1"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">

                    <div class="mb-4 bg-white p-3 rounded shadow-sm border">
                        <label class="fw-bold text-dark mb-2">اسم المادة</label>
                        <input type="text" name="name" class="form-control form-control-lg" required placeholder="مثال: حاسب آلي">
                    </div>

                    {{-- قسم اختيار الصفوف بواسطة Checkboxes --}}
                    <div class="mb-4 bg-white p-3 rounded shadow-sm border">
                        <label class="fw-bold text-dark mb-3"><i class="fas fa-check-square text-primary me-1"></i> اختر الصفوف الدراسية (يمكنك اختيار أكثر من صف)</label>
                        <div class="row g-2">
                            @foreach($grades as $g) 
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded hover-check bg-light d-flex align-items-center h-100">
                                        <input class="form-check-input cursor-pointer m-0 me-2" type="checkbox" name="grade_ids[]" value="{{ $g->id }}" id="grade_{{ $g->id }}">
                                        <label class="form-check-label fw-bold cursor-pointer text-dark w-100" for="grade_{{ $g->id }}" style="margin-right: 25px;">
                                            {{ $g->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer bg-white p-3 border-top">
                    <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold shadow-sm">حفظ واعتماد المادة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById('addSubjectModal'));
        myModal.show();
    });
</script>
@endif

{{-- ========================== --}}
{{-- 🟡 نافذة تعديل مادة (Edit Modal) --}}
{{-- ========================== --}}
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-warning"></i> تعديل المادة</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subjects.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="subject_id" id="edit_subject_id">
                
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3 bg-white p-3 rounded shadow-sm border">
                        <label class="fw-bold small mb-2 text-dark">الصف الدراسي</label>
                        <select name="grade_id" id="edit_grade_id" class="form-select" required>
                            @foreach($grades as $g) 
                                <option value="{{ $g->id }}">{{ $g->name }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 bg-white p-3 rounded shadow-sm border">
                        <label class="fw-bold small mb-2 text-dark">اسم المادة</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-white p-3 border-top">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .hover-check { transition: all 0.2s ease; border-color: #dee2e6; }
    .hover-check:hover { background-color: #e9ecef !important; border-color: #212529 !important; }
    .form-check-input:checked { background-color: #212529; border-color: #212529; }
</style>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. التعامل مع زر الحذف (SweetAlert باللون الأسود/الأحمر)
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); 
                const form = this.closest('.form-delete');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "سيتم حذف هذه المادة نهائياً!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#212529',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // 2. التعامل مع زر التعديل (تعبئة المودال)
        const editModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
        
        document.querySelectorAll('.btn-edit-subject').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const grade = this.getAttribute('data-grade');

                document.getElementById('edit_subject_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_grade_id').value = grade;

                editModal.show();
            });
        });

        // 3. عرض رسائل النجاح (Toast)
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'تمت العملية!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                iconColor: '#212529'
            });
        @endif
    });
</script>
@endsection