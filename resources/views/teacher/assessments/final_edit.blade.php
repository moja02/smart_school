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

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'خطأ!',
                text: "{{ session('error') }}",
                confirmButtonText: 'موافق'
            });
        </script>
    @endif
    
    {{-- 1. الترويسة الرئيسية (النمط الداكن الموحد) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            
            {{-- معلومات الصفحة --}}
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">
                        رصد الامتحان النهائي
                    </h2>
                    <span class="badge bg-danger text-white border border-light">نهاية الفصل</span>
                </div>
                
                <p class="mb-0 opacity-75">
                    <i class="fas fa-book me-1"></i> المادة: {{ $subject->name }}
                    <span class="mx-2">|</span>
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $section->section }})
                    <span class="mx-2">|</span>
                    <i class="fas fa-star me-1"></i> العظمى: {{ $maxFinal }}
                </p>
            </div>

            {{-- زر العودة والأيقونة --}}
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.class.show', ['subject_id' => $subject->id, 'class_id' => $section->id]) }}" 
                   class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصل
                </a>
                
                <div class="d-none d-md-block ms-3">
                    <i class="fas fa-file-signature fa-4x opacity-25 text-white"></i>
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
                    <strong>الرصد مغلق:</strong> لا يمكنك تعديل درجات النهائي حالياً بناءً على تعليمات الإدارة.
                </div>
            </div>
        </div>
    @endif

    {{-- 2. نموذج الرصد --}}
    <form action="{{ route('teacher.final_grades.store') }}" method="POST">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="section_id" value="{{ $section->id }}">

        <div class="card shadow border-0 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-danger"><i class="fas fa-users me-2"></i> كشف درجات الطلاب</h6>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th width="5%">#</th>
                                <th width="45%" class="text-start ps-4">اسم الطالب</th>
                                <th width="20%">أعمال السنة</th>
                                <th width="30%">درجة النهائي (من {{ $maxFinal }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            @php $studentScore = $scores[$student->id] ?? null; @endphp
                            <tr>
                                <td class="text-muted fw-bold">{{ $index + 1 }}</td>
                                <td class="text-start ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light text-danger rounded-circle me-3 d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">{{ mb_substr($student->name, 0, 1) }}</span>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                        {{ $studentScore->works_score ?? 0 }} درجة
                                    </span>
                                </td>
                                <td>
                                    <div class="input-group justify-content-center mx-auto" style="max-width: 150px;">
                                        <input type="number" 
                                            step="0.5" 
                                            min="0" 
                                            max="{{ $maxFinal }}" 
                                            name="final_marks[{{ $student->id }}]" 
                                            value="{{ $studentScore->final_score ?? '' }}" 
                                            class="form-control text-center fw-bold fs-5 text-danger border-danger {{ $isLocked ? 'bg-light' : '' }}" 
                                            placeholder="-"
                                            {{ $isLocked ? 'readonly' : '' }}>
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
                <div class="card-footer bg-white py-4 text-center border-top sticky-bottom shadow-sm">
                    <button type="submit" class="btn btn-danger px-5 py-2 fw-bold shadow hover-scale rounded-pill">
                        <i class="fas fa-check-double me-2"></i> اعتماد الدرجات النهائية
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
    /* إخفاء أسهم الإدخال الرقمي لجمالية أكثر */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
@endsection