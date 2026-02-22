@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">المواد الدراسية (توزيع الحصص) 📚</h3>
            <p class="mb-0 opacity-75">إدارة المواد العامة والخاصة وتوزيع الحصص.</p>
        </div>
        <div class="d-flex gap-2">
            {{-- زر تعديل الهيكلية --}}
            <a href="{{ route('admin.settings.structure') }}" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-cogs me-1"></i> إعداد المراحل
            </a>
            
            {{-- زر إضافة مادة --}}
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
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

{{-- ✅ التعديل الجوهري: تجميع الصفوف حسب المرحلة ديناميكياً --}}
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
        <h4 class="fw-bold text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-layer-group me-2"></i> {{ $stageNames[$stageKey] ?? $stageKey }}
        </h4>
        
        <div class="row">
            @foreach($stageGrades as $grade)
            <div class="col-md-6 mb-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">{{ $grade->name }}</h6>
                        <span class="badge bg-warning text-dark">
                            {{ $grade->subjects->sum(fn($sub) => $sub->getClassesCount()) }} حصة
                        </span>
                    </div>
                    
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 w-50">المادة</th>
                                    <th class="text-center w-25">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grade->subjects as $subject)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold text-dark">{{ $subject->name }}</span>
                                        @if($subject->school_id != null)
                                            <i class="fas fa-school text-info small ms-1" title="مادة خاصة"></i>
                                        @endif
                                    </td>
                                    

                                    {{-- عمود الإجراءات (تعديل كامل / حذف) --}}
                                    <td class="text-center">
                                        @if($subject->school_id != null)
                                            {{-- زر التعديل الكامل --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-link text-primary p-0 me-2 btn-edit-subject" 
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
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-delete" title="حذف">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small"><i class="fas fa-lock opacity-50"></i></span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-3 text-muted small">لا توجد مواد.</td></tr>
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
    <div class="text-center py-5">
        <div class="mb-3 opacity-50"><i class="fas fa-layer-group fa-4x"></i></div>
        <h4>لم يتم تحديد المراحل الدراسية بعد!</h4>
        <p class="text-muted">يرجى الذهاب لإعدادات الهيكلية وتحديد المراحل التي تدرسها مدرستك.</p>
        <a href="{{ route('admin.settings.structure') }}" class="btn btn-primary">إعداد المراحل الآن</a>
    </div>
@endforelse

{{-- ========================== --}}
{{-- 🟢 نافذة إضافة مادة (Modal) --}}
{{-- ========================== --}}
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold">إضافة مادة خاصة جديدة</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">الصف الدراسي</label>
                        {{-- ✅ القائمة المنسدلة تعرض الصفوف المتاحة فقط --}}
                        <select name="grade_id" class="form-select" required>
                            @foreach($grades as $g) 
                                <option value="{{ $g->id }}">{{ $g->name }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">اسم المادة</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">عدد الحصص</label>
                        <input type="number" name="weekly_classes" class="form-control" value="2" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="submit" class="btn btn-primary w-100">حفظ المادة</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================== --}}
{{-- 🟡 نافذة تعديل مادة (Edit Modal) --}}
{{-- ========================== --}}
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-1"></i> تعديل مادة خاصة</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subjects.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="subject_id" id="edit_subject_id">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">الصف الدراسي</label>
                        <select name="grade_id" id="edit_grade_id" class="form-select" required>
                            @foreach($grades as $g) 
                                <option value="{{ $g->id }}">{{ $g->name }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">اسم المادة</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">عدد الحصص</label>
                        <input type="number" name="weekly_classes" id="edit_weekly_classes" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="submit" class="btn btn-warning w-100 fw-bold">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. التعامل مع زر الحذف (SweetAlert)
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // منع الإرسال الفوري
                const form = this.closest('.form-delete');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "سيتم حذف هذه المادة نهائياً!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
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
                const classes = this.getAttribute('data-classes');
                const grade = this.getAttribute('data-grade');

                document.getElementById('edit_subject_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_weekly_classes').value = classes;
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
                showConfirmButton: false
            });
        @endif
    });
</script>
@endsection