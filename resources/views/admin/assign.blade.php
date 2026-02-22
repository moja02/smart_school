@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow border-0">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">إسناد المواد للمعلمين 👨‍🏫</h2>
            <p class="mb-0 opacity-75">اختر الصف والمادة لتحديد الشعب الدراسية لكل معلم.</p>
        </div>
        <div class="d-none d-md-block opacity-25">
            <i class="fas fa-project-diagram fa-4x"></i>
        </div>
    </div>
</div>

<div class="card shadow border-0 mb-4 bg-light">
    <div class="card-body">
        <form action="{{ route('admin.assign') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="small fw-bold text-secondary">1. اختر الصف الدراسي:</label>
                    <select name="grade_id" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">-- اختر الصف --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(isset($subjects) && $subjects->count() > 0)
                <div class="col-md-5">
                    <label class="small fw-bold text-secondary">2. اختر المادة:</label>
                    <select name="subject_id" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">-- اختر المادة --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="col-md-2">
                    <a href="{{ route('admin.assign') }}" class="btn btn-outline-secondary w-100 shadow-sm"><i class="fas fa-sync"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(request('subject_id') && isset($sections))
<form action="{{ route('admin.assign.store') }}" method="POST">
    @csrf
    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-tie me-2"></i> 3. تحديد المعلم</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-center py-3 bg-light rounded-3">
                        <i class="fas fa-chalkboard-teacher fa-3x text-secondary mb-2"></i>
                        <p class="small text-muted mb-0">سيتم إسناد الشعب المختارة لهذا المعلم</p>
                    </div>
                    <select name="teacher_id" class="form-select form-select-lg shadow-sm mb-3" required>
                        <option value="">-- اختر المعلم --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold rounded-pill mt-2">
                        <i class="fas fa-check-circle me-1"></i> حفظ الإسناد
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-th-list me-2"></i> 4. الشعب الدراسية</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($sections as $sec)
                        {{-- تعريف البيانات الخاصة بالشعبة الحالية --}}
                        @php 
                            $assignment = $assignedSections[$sec->id] ?? null;
                            $teacherName = $assignment ? $assignment->teacher_name : null;
                            $teacherId = $assignment ? $assignment->teacher_id : null;
                        @endphp
                        <label class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                            <div class="d-flex align-items-center">
                                {{-- التشيك بوكس --}}
                                <input class="form-check-input me-3" type="checkbox" name="section_ids[]" value="{{ $sec->id }}" 
                                    {{ $teacherName ? 'checked disabled' : '' }} style="width: 1.5rem; height: 1.5rem;">
                                
                                <span class="{{ $teacherName ? 'text-muted text-decoration-line-through' : 'fw-bold fs-5' }}">
                                    شعبة: {{ $sec->section }}
                                </span>
                            </div>
                            
                            @if($teacherName)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                        <i class="fas fa-user-lock me-1"></i> {{ $teacherName }}
                                    </span>
                                    
                                    {{-- زر التعديل الجديد --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary border-0" 
                                        onclick="openEditModal('{{ $sec->id }}', '{{ $sec->section }}', '{{ $teacherId }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- زر الحذف القديم --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" 
                                            onclick="confirmDelete('{{ $sec->id }}', '{{ request('subject_id') }}', '{{ $teacherName }}', '{{ $sec->section }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            @else
                                {{-- حالة: متاح --}}
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                    <i class="fas fa-check me-1"></i> متاحة
                                </span>
                            @endif
                        </label>
                        @empty
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <p class="text-muted">لا توجد شعب دراسية مضافة لهذا الصف.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endif
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">تغيير أستاذ المادة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.assign.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-4">
                    <input type="hidden" name="section_id" id="modal_section_id">
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    
                    <p class="text-muted mb-3">أنت بصدد تغيير أستاذ المادة لشعبة: <strong id="modal_section_name" class="text-dark"></strong></p>
                    
                    <label class="fw-bold small mb-2 text-secondary">اختر الأستاذ الجديد:</label>
                    <select name="teacher_id" id="modal_teacher_id" class="form-select form-select-lg shadow-sm" required>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">تأكيد التغيير</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function confirmDelete(sectionId, subjectId, teacherName, sectionName) {
    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: `سيتم إلغاء ربط الأستاذ (${teacherName}) بشعبة (${sectionName})`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، إلغاء',
        cancelButtonText: 'تراجع'
    }).then((result) => {
        if (result.isConfirmed) {
            // إنشاء فورم ديناميكي
            let form = document.createElement('form');
            form.method = 'POST';
            
            // 🔥 هنا السر: توليد الرابط الصحيح من لارافيل مباشرة
            // نضع placeholder ونستبدله بالـ ID الفعلي بالجافاسكربت
            let urlTemplate = "{{ route('admin.assign.remove', ':id') }}";
            form.action = urlTemplate.replace(':id', sectionId) + "?subject_id=" + subjectId;
            
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
<script>
function openEditModal(sectionId, sectionName, currentTeacherId) {
    document.getElementById('modal_section_id').value = sectionId;
    document.getElementById('modal_section_name').innerText = sectionName;
    document.getElementById('modal_teacher_id').value = currentTeacherId;
    
    var myModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
    myModal.show();
}
</script>
