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
            
            {{-- المعلومات الرئيسية --}}
            <div>
                <div class="d-flex align-items-center mb-1">
                    <h2 class="fw-bold text-white mb-0 me-3">
                        رصد الحضور والغياب
                    </h2>
                    <span class="badge bg-warning text-dark">{{ $grade->name }}</span>
                </div>
                
                <p class="mb-0 opacity-75">
                    <i class="fas fa-layer-group me-1"></i> الشعبة: ({{ $section->section }}) 
                    <span class="mx-2">|</span>
                    <i class="fas fa-calendar-alt me-1"></i> التاريخ: {{ date('Y-m-d') }}
                </p>
            </div>

            {{-- الأزرار والأيقونة الخلفية --}}
            <div class="d-flex align-items-center gap-3">
                {{-- إحصائية سريعة --}}
                <div class="text-center d-none d-lg-block border-end border-secondary pe-3 me-2">
                    <small class="d-block text-white-50">إجمالي الطلاب</small>
                    <span class="fw-bold fs-5">{{ $students->count() }}</span>
                </div>

                {{-- زر العودة --}}
                <a href="{{ url()->previous() }}" class="btn btn-light btn-sm rounded-pill text-primary fw-bold">
                    <i class="fas fa-arrow-right me-1"></i> عودة
                </a>
                
                {{-- الأيقونة الخلفية --}}
                <div class="d-none d-md-block ms-3">
                    <i class="fas fa-user-check fa-4x opacity-25 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. نموذج الرصد --}}
    <form action="{{ route('teacher.attendance.store') }}" method="POST">
        @csrf
        <input type="hidden" name="section_id" value="{{ $section->id }}">

        <div class="card shadow border-0 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list-ol me-2"></i> قائمة الطلاب</h6>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th class="text-start ps-4" style="width: 40%">اسم الطالب</th>
                                <th style="width: 55%">حالة الحضور</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            @php $status = $todayAttendance[$student->id] ?? 'present'; @endphp
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
                                    {{-- أزرار الرصد التفاعلية --}}
                                    <div class="btn-group shadow-sm w-75" role="group">
                                        {{-- حاضر --}}
                                        <input type="radio" class="btn-check" name="attendance[{{ $student->id }}]" id="p{{ $student->id }}" value="present" {{ $status == 'present' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success py-2" for="p{{ $student->id }}">
                                            <i class="fas fa-check me-1"></i> حاضر
                                        </label>

                                        {{-- غائب --}}
                                        <input type="radio" class="btn-check" name="attendance[{{ $student->id }}]" id="a{{ $student->id }}" value="absent" {{ $status == 'absent' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger py-2" for="a{{ $student->id }}">
                                            <i class="fas fa-times me-1"></i> غائب
                                        </label>

                                        {{-- متأخر --}}
                                        <input type="radio" class="btn-check" name="attendance[{{ $student->id }}]" id="l{{ $student->id }}" value="late" {{ $status == 'late' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning py-2" for="l{{ $student->id }}">
                                            <i class="fas fa-clock me-1"></i> متأخر
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- زر الحفظ العائم في الفوتر --}}
            <div class="card-footer bg-white py-4 text-center sticky-bottom border-top">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow hover-scale rounded-pill">
                    <i class="fas fa-save me-2"></i> حفظ واعتماد كشف الحضور
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    /* تحسين ألوان الأزرار عند التحديد */
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; box-shadow: 0 4px 6px rgba(25, 135, 84, 0.3); }
    .btn-check:checked + .btn-outline-danger { background-color: #dc3545; color: white; box-shadow: 0 4px 6px rgba(220, 53, 69, 0.3); }
    .btn-check:checked + .btn-outline-warning { background-color: #ffc107; color: black; box-shadow: 0 4px 6px rgba(255, 193, 7, 0.3); }
    
    .table-hover tbody tr:hover { background-color: #fcfcfc; }
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-2px); }
    
    /* تنسيق خاص للأفاتار */
    .avatar-sm { font-size: 1.1rem; }
</style>
@endsection