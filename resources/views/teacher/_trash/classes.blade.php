@extends('layouts.teacher')

@section('content')
<div class="container-fluid py-4">

    {{-- ترويسة الفصل --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-body p-4 bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">
                            <i class="fas fa-chalkboard-teacher me-2"></i> الفصل: {{ $class->name }}
                        </h2>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-users me-1"></i> عدد الطلاب: {{ $class->students->count() }} طالب
                            | <i class="fas fa-building me-1"></i> المرحلة: {{ $class->grade_level ?? 'عام' }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('teacher.classes') }}" class="btn btn-light text-primary fw-bold rounded-pill px-4 shadow-sm">
                            <i class="fas fa-arrow-right me-2"></i> العودة للفصول
                        </a>
                    </div>
                </div>
                {{-- شريط الإجراءات السريعة --}}
                <div class="card-footer bg-white p-3 border-top">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <a href="{{ route('teacher.attendance', $class->id) }}" class="btn btn-outline-primary w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-user-check"></i> رصد الغياب
                            </a>
                        </div>
                        {{-- ملاحظة: أزرار المواد والدرجات تظهر في الأسفل لأنها تحتاج لاختيار المادة --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- قائمة الطلاب --}}
        <div class="col-lg-8">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark"><i class="fas fa-list-ul text-primary me-2"></i> قائمة الطلاب</h5>
                    <div class="input-group w-auto">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                        <input type="text" id="studentSearch" class="form-control border-0 bg-light" placeholder="بحث عن طالب...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>اسم الطالب</th>
                                    <th>بيانات ولي الأمر</th>
                                    <th class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTable">
                                @forelse($class->students as $index => $student)
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                {{ substr($student->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $student->user->name }}</h6>
                                                <small class="text-muted">{{ $student->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($student->parent)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-shield text-secondary me-2"></i>
                                                <div>
                                                    <span class="d-block text-dark small">{{ $student->parent->user->name ?? 'غير متوفر' }}</span>
                                                    <a href="tel:{{ $student->parent->phone }}" class="text-decoration-none small text-muted">
                                                        {{ $student->parent->phone ?? '' }}
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted fw-normal">غير مرتبط</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu border-0 shadow dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('messages.chat', $student->user->id) }}">
                                                        <i class="fas fa-comment-dots text-primary me-2"></i> مراسلة الطالب
                                                    </a>
                                                </li>
                                                @if($student->parent)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('messages.chat', $student->parent->user->id) }}">
                                                        <i class="fas fa-envelope text-warning me-2"></i> مراسلة ولي الأمر
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i>
                                        <p>لا يوجد طلاب مسجلين في هذا الفصل حالياً.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- القائمة الجانبية: المواد الدراسية لهذا الفصل --}}
        <div class="col-lg-4">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="fas fa-book text-success me-2"></i> مواد هذا الفصل</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">اختر مادة للبدء في إضافة الدروس، الاختبارات، أو رصد الدرجات.</p>
                    
                    <div class="list-group list-group-flush">
                        {{-- ملاحظة: يفترض أن العلاقة بين المعلم والفصل والمادة موجودة في teacher_subject --}}
                        @php
                            // جلب المواد التي يدرسها هذا المعلم لهذا الفصل بالتحديد
                            $teacherSubjects = DB::table('teacher_subject')
                                ->join('subjects', 'teacher_subject.subject_id', '=', 'subjects.id')
                                ->where('teacher_subject.teacher_id', Auth::id())
                                ->where('teacher_subject.class_id', $class->id)
                                ->select('subjects.*')
                                ->get();
                        @endphp

                        @forelse($teacherSubjects as $subject)
                            <div class="list-group-item p-3 border rounded mb-2 bg-light hover-shadow transition-all">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-primary">{{ $subject->name }}</h6>
                                    <span class="badge bg-white text-dark border">المادة</span>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('teacher.subject.show', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" 
                                       class="btn btn-sm btn-primary">
                                       <i class="fas fa-eye me-1"></i> إدارة المحتوى
                                    </a>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('teacher.assessments.index', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="btn btn-outline-secondary">
                                            التقييمات
                                        </a>
                                        <a href="{{ route('teacher.subject.report', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="btn btn-outline-secondary">
                                            التقارير
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle text-warning mb-2 fa-2x"></i>
                                <p class="text-muted small mb-0">أنت لا تدرس أي مادة لهذا الفصل.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // كود بسيط للبحث في الجدول
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        let searchText = this.value.toLowerCase();
        let rows = document.querySelectorAll('#studentsTable tr');
        
        rows.forEach(row => {
            let name = row.cells[1].textContent.toLowerCase();
            if(name.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<style>
    .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition-all { transition: all 0.3s ease; }
</style>
@endsection