@extends('layouts.admin')

@section('content')

{{-- 1. ترويسة الصفحة بنفس ستايل الداشبورد --}}
<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">إدارة الطلاب 👨‍🎓</h2>
            <p class="mb-0 opacity-75">أهلاً بك.. يمكنك هنا البحث عن الطلاب، توزيعهم، ونقلهم بين الفصول والشعب.</p>
        </div>
        <div class="text-end">
            {{-- زر النقل الجماعي (يظهر عند التحديد) --}}
            <button type="button" id="btn-transfer" class="btn btn-warning shadow-sm fw-bold d-none animate__animated animate__bounceIn" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="fas fa-exchange-alt me-2"></i> نقل المحدد (<span id="selected-count">0</span>)
            </button>
            <a href="{{ route('admin.classes') }}" class="btn btn-light shadow-sm text-primary fw-bold ms-2">
                <i class="fas fa-arrow-right me-2"></i> رجوع
            </a>
        </div>
    </div>
</div>

{{-- 2. قسم الفلترة (البحث) --}}
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-search me-2"></i> تصفية الطلاب حسب الصف والشعبة</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.students') }}" method="GET" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-secondary">اختر الصف الدراسي:</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-university text-primary"></i></span>
                    <select name="grade_id" class="form-select border-start-0" onchange="this.form.submit()">
                        <option value="">-- كل الصفوف --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-5">
                <label class="form-label small fw-bold text-secondary">اختر الشعبة:</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-users text-success"></i></span>
                    <select name="class_id" class="form-select border-start-0" onchange="this.form.submit()">
                        <option value="">-- كل الشعب --</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" {{ request('class_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->section }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <a href="{{ route('admin.students') }}" class="btn btn-outline-secondary w-100 shadow-sm fw-bold">
                    <i class="fas fa-sync-alt"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 3. جدول الطلاب (تم حذف عمود الإجراءات) --}}
<form action="{{ route('admin.students.bulk_transfer') }}" method="POST" id="bulkForm">
    @csrf
    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th class="py-3">اسم الطالب</th>
                            <th class="text-center">الفصل / الشعبة الحالية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="{{ $student->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle text-primary d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                        <i class="fas fa-user-graduate fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $student->name }}</div>
                                        <div class="small text-muted">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($student->studentProfile && $student->studentProfile->schoolClass)
                                    <span class="badge bg-info text-dark border px-3 py-2 shadow-sm fw-bold">
                                        {{ $student->studentProfile->schoolClass->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-3 py-2 fw-normal">غير موزع</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-light mb-3"></i>
                                <p class="text-muted">لا يوجد طلاب مطابقين للبحث حالياً.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- نافذة النقل (Modal) --}}
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-random me-2"></i> نقل الطلاب المحددين</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold mb-3">اختر الفصل الجديد المراد النقل إليه:</label>
                    <select name="new_class_id" class="form-select form-select-lg shadow-sm border-2 border-warning" required>
                        <option value="" disabled selected>-- اختر الفصل الهدف --</option>
                        @php
                            $transferClasses = \App\Models\SchoolClass::where('school_id', auth()->user()->school_id)->get();
                        @endphp
                        @foreach($transferClasses as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow">تأكيد النقل</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const btnTransfer = document.getElementById('btn-transfer');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateButtonState() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            let count = Array.from(checkboxes).filter(cb => cb.checked).length;
            
            selectedCountSpan.innerText = count;
            if (count > 0) {
                btnTransfer.classList.remove('d-none');
            } else {
                btnTransfer.classList.add('d-none');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = this.checked);
                updateButtonState();
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('student-checkbox')) {
                updateButtonState();
            }
        });
    });
</script>
@endsection