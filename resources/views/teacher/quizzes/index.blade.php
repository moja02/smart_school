@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    {{-- مكتبة التنبيهات --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>Swal.fire({ icon: 'success', title: 'تمت العملية', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false, toast: true, position: 'top-end' });</script>
    @endif

    {{-- 1. الترويسة الرئيسية (Dark Header) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">إدارة الاختبارات</h2>
                    <span class="badge bg-warning text-dark">{{ $subject->name }}</span>
                </div>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $section->section }}) 
                    <span class="mx-2">|</span>
                    <i class="fas fa-question-circle me-1"></i> بنك الأسئلة والاختبارات الإلكترونية
                </p>
            </div>
            <div class="d-flex align-items-center gap-3">
                 <a href="{{ route('teacher.class.show', ['subject_id' => $subject->id, 'class_id' => $section->id]) }}" 
                   class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصل
                </a>
                <div class="d-none d-md-block ms-3">
                    <i class="fas fa-laptop-code fa-4x opacity-25 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- كرت الإحصائيات السريع --}}
        <div class="col-md-12">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                        <div class="card-body py-3">
                            <h6 class="text-primary fw-bold small">إجمالي الاختبارات</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $quizzes->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                        <div class="card-body py-3">
                            <h6 class="text-success fw-bold small">الاختبارات النشطة</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $quizzes->where('is_active', 1)->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('teacher.quizzes.create', ['subject_id' => $subject->id, 'section_id' => $section->id]) }}" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center shadow fw-bold">
                        <i class="fas fa-plus-circle me-2"></i> إنشاء اختبار جديد
                    </a>
                </div>
            </div>
        </div>

        {{-- جدول الاختبارات --}}
        <div class="col-md-12">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between">
                    <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-list me-2"></i> قائمة الاختبارات المنشأة</h6>
                    {{-- زر لبنك الأسئلة --}}
                    <a href="{{ route('teacher.questions.create', ['subject_id' => $subject->id, 'class_id' => $section->id]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-database me-1"></i> إضافة سؤال لبنك الأسئلة
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">عنوان الاختبار</th>
                                    <th>المدة (دقيقة)</th>
                                    <th>عدد الأسئلة</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $index => $quiz)
                                <tr>
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td class="text-start fw-bold text-primary">{{ $quiz->title }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $quiz->duration }} دقيقة</span></td>
                                    <td>{{ $quiz->questions_count ?? 0 }} سؤال</td>
                                    <td>
                                        @if($quiz->is_active)
                                            <span class="badge bg-success">نشط <i class="fas fa-check ms-1"></i></span>
                                        @else
                                            <span class="badge bg-secondary">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">

                                            <a href="{{ route('teacher.quizzes.show', $quiz->id) }}" class="btn btn-sm btn-outline-primary" title="عرض وطباعة الاختبار">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('teacher.quizzes.results', $quiz->id) }}" class="btn btn-info btn-sm text-white" title="النتائج">
                                                <i class="fas fa-poll me-1"></i> 
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $quiz->id }})" title="حذف">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $quiz->id }}" action="{{ route('teacher.quizzes.delete', $quiz->id) }}" method="POST" style="display: none;">
                                            @csrf @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-clipboard-list fa-3x text-muted opacity-25 mb-3"></i>
                                        <p class="text-muted mb-0">لم تقم بإنشاء أي اختبارات لهذا الفصل بعد.</p>
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
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endsection