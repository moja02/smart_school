@extends('layouts.teacher')

@section('content')
<div class="container py-4">

    {{-- مكتبة التنبيهات --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>Swal.fire({ icon: 'success', title: 'تمت العملية', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });</script>
    @endif

    {{-- فحص حالة القفل --}}
    @php
        $isLocked = \DB::table('schools')->where('id', auth()->user()->school_id)->value('grading_locked');
    @endphp

    {{-- 1. الترويسة الرئيسية (النمط الداكن المعتمد) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            
            {{-- معلومات المادة والفصل --}}
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">
                        {{ $subject->name }}
                    </h2>
                    <span class="badge bg-warning text-dark">{{ $grade->name }}</span>
                </div>
                
                <p class="mb-0 opacity-75">
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $class->section }}) 
                    <span class="mx-2">|</span>
                    <i class="fas fa-users me-1"></i> عدد الطلاب: {{ $students->count() }}
                </p>

                {{-- تنبيه القفل إن وجد --}}
                @if($isLocked)
                    <div class="mt-2">
                        <span class="badge bg-danger">
                            <i class="fas fa-lock me-1"></i> الرصد مغلق من الإدارة
                        </span>
                    </div>
                @endif
            </div>

            {{-- الأزرار والأيقونة --}}
            <div class="d-flex align-items-center gap-3">
                {{-- زر العودة --}}
                <a href="{{ route('teacher.classes') }}" class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصول
                </a>
                
                {{-- الأيقونة الخلفية --}}
                <div class="d-none d-md-block ms-3">
                    <i class="fas fa-book-open fa-4x opacity-25 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. شريط العمليات السريعة --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom-0">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-rocket me-2"></i> عمليات الفصل</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- 1. زر الحضور والغياب --}}
                <div class="col-md col-sm-6">
                    <a href="{{ route('teacher.attendance.index', ['section_id' => $class->id]) }}" class="btn btn-outline-primary w-100 py-3 shadow-sm hover-scale text-decoration-none">
                        <i class="fas fa-user-clock fa-lg d-block mb-2"></i>
                        <span class="fw-bold">رصد الغياب</span>
                    </a>
                </div>

                {{-- 2. زر التقييمات (أعمال السنة) --}}
                <div class="col-md col-sm-6">
                    <a href="{{ route('teacher.assessments.index', ['subject_id' => $subject->id, 'section_id' => $class->id]) }}" class="btn btn-outline-success w-100 py-3 shadow-sm hover-scale text-decoration-none">
                        <i class="fas fa-tasks fa-lg d-block mb-2"></i>
                        <span class="fw-bold">{{ $isLocked ? 'عرض التقييمات' : 'أعمال السنة' }}</span>
                    </a>
                </div>

                {{-- 3.  زر جدول الامتحانات (الجديد) --}}
                <div class="col-md col-sm-6">
                    <a href="{{ route('teacher.schedule.index', ['subject_id' => $subject->id, 'section_id' => $class->id]) }}" class="btn btn-outline-info w-100 py-3 shadow-sm hover-scale text-decoration-none">
                        <i class="fas fa-calendar-alt fa-lg d-block mb-2"></i>
                        <span class="fw-bold">جدول الامتحانات</span>
                    </a>
                </div>

                {{-- 4. زر الاختبارات وبنك الأسئلة --}}
                <div class="col-md col-sm-6">
                    <a href="{{ route('teacher.quizzes.index', ['subject_id' => $subject->id, 'section_id' => $class->id]) }}" class="btn btn-outline-warning text-dark w-100 py-3 shadow-sm hover-scale text-decoration-none">
                        <i class="fas fa-laptop-code fa-lg d-block mb-2"></i>
                        <span class="fw-bold">الاختبارات والأسئلة</span>
                    </a>
                </div>

                {{-- 5. زر النهائي --}}
                <div class="col-md col-sm-12">
                    <a href="{{ route('teacher.final_grades.edit', ['subject_id' => $subject->id, 'section_id' => $class->id]) }}" class="btn btn-outline-danger w-100 py-3 shadow-sm hover-scale text-decoration-none">
                        <i class="fas fa-graduation-cap fa-lg d-block mb-2"></i>
                        <span class="fw-bold">رصد النهائي</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. جدول الطلاب --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark"><i class="fas fa-list-ul text-primary me-2"></i> قائمة الطلاب</h5>
            <div class="input-group w-auto">
                <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                <input type="text" id="studentSearch" class="form-control border-0 bg-light" placeholder="بحث عن اسم طالب...">
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>اسم الطالب</th>
                            <th>بيانات التواصل</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTable">
                        @forelse($students as $index => $student)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 40px; height: 40px;">
                                        <span class="fw-bold">{{ mb_substr($student->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $student->name }}</h6>
                                        <small class="text-muted">{{ $student->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="fas fa-phone-alt me-1 text-primary"></i> {{ $student->phone ?? 'غير متوفر' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu border-0 shadow dropdown-menu-end text-end">
                                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-comment-dots text-primary me-2"></i> مراسلة الطالب</a></li>
                                        <li><a class="dropdown-item py-2" href="#"><i class="fas fa-user-shield text-warning me-2"></i> بيانات ولي الأمر</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">لا يوجد طلاب مسجلين في هذا الفصل حالياً.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- سكريبت البحث --}}
<script>
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        let searchText = this.value.toLowerCase();
        let rows = document.querySelectorAll('#studentsTable tr');
        rows.forEach(row => {
            let nameCell = row.cells[1];
            if (nameCell) {
                let name = nameCell.textContent.toLowerCase();
                row.style.display = name.includes(searchText) ? '' : 'none';
            }
        });
    });
</script>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-3px); }
    .avatar-sm { font-size: 1.1rem; }
</style>
@endsection