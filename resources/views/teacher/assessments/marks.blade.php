@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    {{-- مكتبة SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'تم الحفظ!',
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
            
            {{-- معلومات التقييم --}}
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">
                        رصد الدرجات
                    </h2>
                    <span class="badge bg-primary text-white border border-light">{{ $assessment->name }}</span>
                </div>
                
                <p class="mb-0 opacity-75">
                    <i class="fas fa-book me-1"></i> المادة: {{ $subject->name }}
                    <span class="mx-2">|</span>
                    <i class="fas fa-star me-1"></i> الدرجة العظمى: {{ $assessment->max_score }}
                </p>
            </div>

            {{-- زر العودة --}}
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.assessments.index', ['subject_id' => $subject->id, 'section_id' => $section->id]) }}" 
                   class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للتقييمات
                </a>
                
                <div class="d-none d-md-block ms-3">
                    <i class="fas fa-pen-nib fa-4x opacity-25 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- تنبيه القفل --}}
    @if($isLocked)
        <div class="alert alert-danger shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
            <div class="d-flex align-items-center">
                <i class="fas fa-lock fa-lg me-3"></i>
                <div>
                    <strong>وضع العرض فقط:</strong> لا يمكنك تعديل الدرجات حالياً لأن الإدارة أغلقت الرصد.
                </div>
            </div>
        </div>
    @endif

    {{-- 2. نموذج الرصد --}}
    <form action="{{ route('teacher.assessments.save_marks') }}" method="POST">
        @csrf
        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

        <div class="card shadow border-0 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-users me-2"></i> قائمة الطلاب</h6>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th class="text-start ps-4" style="width: 50%">اسم الطالب</th>
                                <th style="width: 45%">الدرجة المستحقة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            <tr>
                                <td class="text-muted fw-bold">{{ $index + 1 }}</td>
                                <td class="text-start ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light text-primary rounded-circle me-3 d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">{{ mb_substr($student->name, 0, 1) }}</span>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group justify-content-center" style="max-width: 180px; margin: 0 auto;">
                                        <input type="number" 
                                            step="0.5" 
                                            min="0" 
                                            max="{{ $assessment->max_score }}" 
                                            name="marks[{{ $student->id }}]" 
                                            value="{{ $marks[$student->id] ?? '' }}" 
                                            class="form-control text-center fw-bold fs-5 text-primary border-primary {{ $isLocked ? 'bg-light' : '' }}" 
                                            {{ $isLocked ? 'readonly' : '' }}
                                            placeholder="-"
                                        >
                                        <span class="input-group-text bg-white text-muted border-primary">/ {{ $assessment->max_score }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- فوتر الحفظ --}}
            @if(!$isLocked)
                <div class="card-footer bg-white text-center py-4 border-top sticky-bottom shadow-sm">
                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow hover-scale rounded-pill">
                        <i class="fas fa-save me-2"></i> حفظ واعتماد الدرجات
                    </button>
                </div>
            @else
                <div class="card-footer bg-light text-center py-3">
                    <span class="text-muted small"><i class="fas fa-ban me-1"></i> الحفظ غير متاح حالياً</span>
                </div>
            @endif
        </div>
    </form>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-2px); }
    .avatar-sm { font-size: 1.1rem; }
    /* تنسيق خاص لحقول الإدخال لتكون واضحة */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
@endsection