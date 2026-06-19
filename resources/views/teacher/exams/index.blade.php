@extends('layouts.teacher')
@section('title', 'إدارة الاختبارات الإلكترونية')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-laptop-code text-primary me-2"></i> الاختبارات الإلكترونية</h3>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addExamModal">
            <i class="fas fa-plus"></i> إنشاء اختبار جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        @forelse($exams as $exam)
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 border-top border-primary border-4">
                <div class="card-body">
                    <h5 class="fw-bold">{{ $exam->title }}</h5>
                    <p class="text-muted small mb-2">{{ $exam->description }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-light text-dark"><i class="fas fa-book text-primary"></i> {{ $exam->subject->name }}</span>
                        <span class="badge bg-light text-dark"><i class="fas fa-users text-success"></i> {{ $exam->class->name }}</span>
                        <span class="badge bg-light text-dark"><i class="fas fa-clock text-warning"></i> {{ $exam->duration_minutes }} دقيقة</span>
                    </div>

                    <a href="{{ route('teacher.online_exams.show', $exam->id) }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-cog"></i> إدارة الأسئلة
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-secondary text-center py-5">
                <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                <h5>لا توجد اختبارات مسجلة</h5>
                <p>قم بإنشاء اختبار إلكتروني جديد لطلابك.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Create Exam -->
<div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">إنشاء اختبار إلكتروني جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teacher.online_exams.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان الاختبار</label>
                        <input type="text" name="title" class="form-control" required placeholder="مثال: اختبار منتصف الفصل الأول">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المادة</label>
                            <select name="subject_id" class="form-select" required>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الفصل الدراسي</label>
                            <select name="class_id" class="form-select" required>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">مدة الاختبار (بالدقائق)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="30" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">وصف / تعليمات الاختبار</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-4">حفظ الاختبار</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
