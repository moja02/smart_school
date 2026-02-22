@extends('layouts.teacher')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="fas fa-tasks text-success me-2"></i> التقييمات والاختبارات</h3>
                <p class="mb-0 text-muted">المادة: <strong>{{ $subject->name }}</strong> | الفصل: <strong>{{ $class->name }}</strong></p>
            </div>
            <div>
                <a href="{{ route('teacher.subject.show', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="btn btn-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-right me-1"></i> عودة للمادة
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold m-0 text-success"><i class="fas fa-plus-circle me-2"></i> إضافة تقييم جديد</h5>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success small">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('teacher.assessments.store', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان التقييم <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="مثال: اختبار الشهر الأول، واجب منزلي..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الدرجة العظمى <span class="text-danger">*</span></label>
                        <input type="number" name="max_score" class="form-control" placeholder="مثال: 10، 20..." required min="1">
                    </div>
                    <button type="submit" class="btn btn-success w-100 shadow-sm">
                        <i class="fas fa-save me-1"></i> إنشاء التقييم
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold m-0"><i class="fas fa-list-ul me-2"></i> التقييمات الحالية</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">العنوان</th>
                                <th class="text-center">الدرجة العظمى</th>
                                <th class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assessments as $assessment)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $assessment->title }}</td>
                                <td class="text-center"><span class="badge bg-secondary rounded-pill px-3">{{ $assessment->max_score }}</span></td>
                                <td class="text-center">
                                    {{-- زر رصد الدرجات: يوجه لصفحة الرصد الخاصة بهذا التقييم --}}
                                    <a href="{{ route('teacher.assessments.monitor', ['subject_id' => $subject->id, 'class_id' => $class->id, 'assessment_id' => $assessment->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                        <i class="fas fa-edit me-1"></i> رصد الدرجات
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">لا يوجد تقييمات مضافة لهذه المادة بعد.</p>
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
@endsection