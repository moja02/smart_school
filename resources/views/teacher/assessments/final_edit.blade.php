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
    
    {{-- 1. الترويسة الرئيسية --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            
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
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $section->section ?? '' }})
                    <span class="mx-2">|</span>
                    <i class="fas fa-star me-1"></i> العظمى الكلية: {{ $maxFinal }}
                </p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.class.show', [$subject->id, $section->id]) }}" 
                   class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصل
                </a>
                
                <div class="d-none d-md-block ms-3">
                    <i class="fas fa-file-signature fa-4x opacity-25 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($isLocked) && $isLocked)
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
                                <th width="30%" class="text-start ps-4">اسم الطالب</th>
                                <th width="15%">أعمال السنة</th>
                                <th width="20%">
                                    النهائي (الفصل 1)<br>
                                    <span class="badge bg-danger mt-1">من {{ $maxFinalPerSem }}</span>
                                </th>
                                <th width="20%">
                                    النهائي (الفصل 2)<br>
                                    <span class="badge bg-danger mt-1">من {{ $maxFinalPerSem }}</span>
                                </th>
                                <th width="10%">المجموع النهائي</th>
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
                                        {{ $studentScore->works_score ?? 0 }} 
                                    </span>
                                </td>
                                
                                {{-- حقل درجة الفصل الأول --}}
                                <td>
                                    <div class="input-group justify-content-center mx-auto" style="max-width: 120px;">
                                        <input type="number" step="0.5" min="0" max="{{ $maxFinalPerSem }}" 
                                            name="final_marks_sem1[{{ $student->id }}]" 
                                            value="{{ $studentScore->final_score_sem1 ?? '' }}" 
                                            class="form-control text-center fw-bold fs-6 border-danger sem-input sem1-input {{ (!empty($isLocked) && $isLocked) ? 'bg-light' : '' }}" 
                                            placeholder="-" {{ (!empty($isLocked) && $isLocked) ? 'readonly' : '' }}>
                                    </div>
                                </td>

                                {{-- حقل درجة الفصل الثاني --}}
                                <td>
                                    <div class="input-group justify-content-center mx-auto" style="max-width: 120px;">
                                        <input type="number" step="0.5" min="0" max="{{ $maxFinalPerSem }}" 
                                            name="final_marks_sem2[{{ $student->id }}]" 
                                            value="{{ $studentScore->final_score_sem2 ?? '' }}" 
                                            class="form-control text-center fw-bold fs-6 border-danger sem-input sem2-input {{ (!empty($isLocked) && $isLocked) ? 'bg-light' : '' }}" 
                                            placeholder="-" {{ (!empty($isLocked) && $isLocked) ? 'readonly' : '' }}>
                                    </div>
                                </td>

                                {{-- مجموع النهائي --}}
                                <td>
                                    <span class="fw-bold fs-5 text-dark total-label">
                                        {{ ($studentScore->final_score_sem1 ?? 0) + ($studentScore->final_score_sem2 ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if(empty($isLocked) || !$isLocked)
                <div class="card-footer bg-white py-4 text-center border-top sticky-bottom shadow-sm">
                    <button type="submit" class="btn btn-danger px-5 py-2 fw-bold shadow hover-scale rounded-pill">
                        <i class="fas fa-check-double me-2"></i> اعتماد الدرجات النهائية للفصلين
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

{{-- سكربت لحساب المجموع التلقائي و منع تخطي الحد --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        const maxAllowed = {{ $maxFinalPerSem ?? 0 }};

        rows.forEach(row => {
            const sem1Input = row.querySelector('.sem1-input');
            const sem2Input = row.querySelector('.sem2-input');
            const totalLabel = row.querySelector('.total-label');

            if(sem1Input && sem2Input) {
                const calculateTotal = () => {
                    // منع إدخال رقم أكبر من المسموح
                    if(parseFloat(sem1Input.value) > maxAllowed) sem1Input.value = maxAllowed;
                    if(parseFloat(sem2Input.value) > maxAllowed) sem2Input.value = maxAllowed;

                    let val1 = parseFloat(sem1Input.value) || 0;
                    let val2 = parseFloat(sem2Input.value) || 0;
                    
                    totalLabel.textContent = val1 + val2;
                };

                sem1Input.addEventListener('input', calculateTotal);
                sem2Input.addEventListener('input', calculateTotal);
            }
        });
    });
</script>
@endsection