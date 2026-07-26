@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow border-0">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">إسناد المواد للمعلمين 👨‍🏫</h2>
            <p class="mb-0 opacity-75">اختر المادة لتحديد الفصول الدراسية لكل معلم دفعة واحدة.</p>
        </div>
        <div class="d-none d-md-block opacity-25">
            <i class="fas fa-project-diagram fa-4x"></i>
        </div>
    </div>
</div>

<div class="card shadow border-0 mb-4 bg-light">
    <div class="card-body">
        <form action="{{ route('admin.assign') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end justify-content-center">
                
                @if(isset($subjectNames) && count($subjectNames) > 0)
                <div class="col-md-8">
                    <label class="small fw-bold text-secondary">1. اختر المادة:</label>
                    <select name="subject_name" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">-- اختر المادة --</option>
                        @foreach($subjectNames as $subject)
                            <option value="{{ $subject }}" {{ request('subject_name') == $subject ? 'selected' : '' }}>{{ $subject }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-md-8 text-center text-muted">
                    <i class="fas fa-exclamation-circle text-warning me-1"></i> لا توجد مواد مسجلة في المدرسة بعد.
                </div>
                @endif
                
                <div class="col-md-2">
                    <a href="{{ route('admin.assign') }}" class="btn btn-outline-secondary w-100 shadow-sm"><i class="fas fa-sync"></i> إعادة تعيين</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(request('subject_name') && isset($sectionsByGrade) && $sectionsByGrade->count() > 0)
<form action="{{ route('admin.assign.store') }}" method="POST">
    @csrf
    <input type="hidden" name="subject_name" value="{{ request('subject_name') }}">
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-tie me-2"></i> 2. تحديد المعلم</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-center py-3 bg-light rounded-3">
                        <i class="fas fa-chalkboard-teacher fa-3x text-secondary mb-2"></i>
                        <p class="small text-muted mb-0">سيتم إسناد الفصول المختارة لهذا المعلم</p>
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
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-th-list me-2"></i> 3. الفصول الدراسية التي تدرس مادة ({{ request('subject_name') }})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="gradesAccordion">
                        @foreach($sectionsByGrade as $gradeName => $sections)
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} bg-light fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $loop->index }}">
                                    <i class="fas fa-layer-group text-primary me-2 opacity-50"></i> {{ $gradeName }}
                                    <span class="badge bg-secondary ms-2 rounded-pill">{{ $sections->count() }} فصول</span>
                                </button>
                            </h2>
                            <div id="collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $loop->index }}" data-bs-parent="#gradesAccordion">
                                <div class="accordion-body p-0">
                                    <div class="list-group list-group-flush">
                                        @foreach($sections as $sec)
                                        @php 
                                            $assignment = $assignedSections[$sec->id] ?? null;
                                            $teacherName = $assignment ? $assignment->teacher_name : null;
                                            $teacherId = $assignment ? $assignment->teacher_id : null;
                                        @endphp
                                        <label class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-start-0 border-end-0">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input me-3" type="checkbox" name="section_ids[]" value="{{ $sec->id }}" 
                                                    {{ $teacherName ? 'checked disabled' : '' }} style="width: 1.5rem; height: 1.5rem;">
                                                
                                                <span class="{{ $teacherName ? 'text-muted text-decoration-line-through' : 'fw-bold fs-5' }}">
                                                    فصل: {{ $sec->section }}
                                                </span>
                                            </div>
                                            
                                            @if($teacherName)
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                                        <i class="fas fa-user-lock me-1"></i> {{ $teacherName }}
                                                    </span>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-primary border-0" 
                                                        onclick="openEditModal('{{ $sec->id }}', '{{ $sec->section }}', '{{ $teacherId }}', '{{ $gradeName }}')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" 
                                                            onclick="confirmDelete('{{ $sec->id }}', '{{ request('subject_name') }}', '{{ $teacherName }}', '{{ $sec->section }}')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                                    <i class="fas fa-check me-1"></i> متاح
                                                </span>
                                            @endif
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@elseif(request('subject_name'))
    <div class="text-center py-5">
        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
        <h5 class="text-muted">لا توجد فصول دراسية تدرس مادة ({{ request('subject_name') }}).</h5>
    </div>
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
                    <input type="hidden" name="subject_name" value="{{ request('subject_name') }}">
                    
                    <p class="text-muted mb-3">أنت بصدد تغيير أستاذ المادة لفصل: <strong id="modal_section_name" class="text-dark"></strong> التابع لـ <strong id="modal_grade_name" class="text-dark"></strong></p>
                    
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
function confirmDelete(sectionId, subjectName, teacherName, sectionName) {
    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: `سيتم إلغاء ربط الأستاذ (${teacherName}) بفصل (${sectionName})`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، إلغاء',
        cancelButtonText: 'تراجع'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            
            let urlTemplate = "{{ route('admin.assign.remove', ':id') }}";
            form.action = urlTemplate.replace(':id', sectionId) + "?subject_name=" + encodeURIComponent(subjectName);
            
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function openEditModal(sectionId, sectionName, currentTeacherId, gradeName) {
    document.getElementById('modal_section_id').value = sectionId;
    document.getElementById('modal_section_name').innerText = sectionName;
    document.getElementById('modal_grade_name').innerText = gradeName;
    document.getElementById('modal_teacher_id').value = currentTeacherId;
    
    var myModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
    myModal.show();
}
</script>
@endsection
