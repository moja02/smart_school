@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    
    {{-- مكتبة SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'تمت العملية بنجاح!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000,
                toast: true,
                position: 'top-end'
            });
        </script>
    @endif

    {{-- 1. الترويسة الرئيسية (النمط الداكن الموحد) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            
            {{-- معلومات المادة --}}
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">
                        إدارة التقييمات
                    </h2>
                    <span class="badge bg-warning text-dark">{{ $subject->name }}</span>
                </div>
                
                <p class="mb-0 opacity-75">
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $section->section }}) 
                    <span class="mx-2">|</span>
                    <i class="fas fa-calculator me-1"></i> إدارة درجات أعمال السنة
                </p>
            </div>

            {{-- إحصائيات الدرجات وزر العودة --}}
            <div class="d-flex align-items-center gap-4">
                
                {{-- مؤشر رصيد الدرجات --}}
                <div class="text-center px-3 border-end border-secondary">
                    <small class="d-block text-white-50 mb-1">المجموع الحالي</small>
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="fw-bold fs-4 {{ $currentTotalMax > $allowedMaxWorks ? 'text-danger' : 'text-success' }}">
                            {{ $currentTotalMax }}
                        </span>
                        <span class="text-white-50 mx-1">/</span>
                        <span class="fs-6 text-white-50">{{ $allowedMaxWorks }}</span>
                    </div>
                </div>

                {{-- زر العودة --}}
                <a href="{{ route('teacher.class.show', ['subject_id' => $subject->id, 'class_id' => $section->id]) }}" 
                   class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصل
                </a>
            </div>
        </div>
    </div>

    {{-- تنبيه إذا كان الرصد مغلقاً --}}
    @if($isLocked)
        <div class="alert alert-danger shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
            <div class="d-flex align-items-center">
                <i class="fas fa-lock fa-2x me-3"></i>
                <div>
                    <h5 class="fw-bold mb-1">نظام الرصد مغلق</h5>
                    <p class="mb-0">لا يمكنك إضافة تقييمات جديدة أو تعديل الدرجات الحالية بأمر من الإدارة.</p>
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
                    <form action="{{ route('teacher.assessments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <input type="hidden" name="section_id" value="{{ $section->id }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">اسم التقييم</label>
                            <input type="text" name="name" class="form-control" placeholder="مثلاً: اختبار قصير 1" required {{ $isLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">الدرجة العظمى</label>
                            <div class="input-group">
                                <input type="number" name="max_score" step="1" min="1" class="form-control" placeholder="0" required {{ $isLocked ? 'disabled' : '' }}>
                                <span class="input-group-text bg-light text-muted">درجة</span>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i> المتبقي من الرصيد: 
                                <span class="fw-bold text-dark">{{ max(0, $allowedMaxWorks - $currentTotalMax) }}</span>
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" 
                            {{ ($allowedMaxWorks - $currentTotalMax) <= 0 || $isLocked ? 'disabled' : '' }}>
                            <i class="fas fa-save me-2"></i> إنشاء التقييم
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- جدول قائمة التقييمات --}}
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
                                    <td class="fw-bold text-dark">{{ $assessment->name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                            {{ $assessment->max_score }} درجة
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('teacher.assessments.marks', $assessment->id) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill shadow-sm">
                                            <i class="fas fa-edit me-1"></i> {{ $isLocked ? 'عرض الدرجات' : 'رصد الدرجات' }}
                                        </a>
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
@endsection