@extends('layouts.teacher')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="fas fa-edit text-warning me-2"></i> رصد الدرجات</h3>
                <p class="mb-0 text-muted">
                    التقييم: <strong>{{ $assessment->title }}</strong> | 
                    الدرجة العظمى: <span class="badge bg-danger">{{ $assessment->max_score }}</span>
                </p>
            </div>
            <div>
                <a href="{{ route('teacher.assessments.index', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="btn btn-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-right me-1"></i> عودة للتقييمات
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body">
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('teacher.assessments.store_grades', ['subject_id' => $subject->id, 'class_id' => $class->id, 'assessment_id' => $assessment->id]) }}" method="POST">
            @csrf
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>اسم الطالب</th>
                            <th style="width: 200px">الدرجة المستحقة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                        @php
                            // جلب الدرجة السابقة إن وجدت
                            $currentMark = $student->assessmentMarks->first() ? $student->assessmentMarks->first()->score : '';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $student->user->name }}</td>
                            <td>
                                <div class="input-group">
                                    <input type="number" 
                                           step="0.5" 
                                           name="grades[{{ $student->id }}]" 
                                           class="form-control text-center fw-bold {{ $currentMark !== '' ? 'border-success text-success' : '' }}" 
                                           value="{{ $currentMark }}" 
                                           max="{{ $assessment->max_score }}" 
                                           min="0"
                                           placeholder="من {{ $assessment->max_score }}">
                                    <span class="input-group-text bg-light">/ {{ $assessment->max_score }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                لا يوجد طلاب مسجلين في هذا الفصل.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> حفظ الدرجات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection