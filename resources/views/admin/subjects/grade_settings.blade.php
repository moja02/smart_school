@extends('layouts.admin')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">توزيع درجات المواد 🎯</h2>
            <p class="mb-0 opacity-75">قم بتحديد توزيع درجات المواد المربوطة بكل صف دراسي.</p>
        </div>
        <div class="d-none d-md-block opacity-25">
            <i class="fas fa-clipboard-check fa-4x"></i>
        </div>
    </div>
</div>

{{-- صندوق الفلترة الذكي --}}
<div class="card shadow border-0 mb-4 bg-light">
    <div class="card-body">
        <form action="{{ route('admin.subjects.grade_settings') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label small fw-bold text-secondary">فلترة حسب الصف الدراسي:</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-filter text-primary"></i></span>
                    <select name="grade_id" class="form-select border-start-0" onchange="this.form.submit()">
                        <option value="">-- عرض مواد جميع الصفوف --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.subjects.grade_settings') }}" class="btn btn-outline-secondary w-100 shadow-sm fw-bold">
                    <i class="fas fa-undo me-1"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>
</div>

{{-- جدول عرض المواد وتوزيع الدرجات --}}
@if($subjects->count() > 0)
<form action="{{ route('admin.subjects.store_grade_settings') }}" method="POST">
    @csrf
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-list me-2"></i> 
                مواد: {{ request('grade_id') ? $grades->where('id', request('grade_id'))->first()->name : 'كافة الصفوف' }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
    <tr>
        <th class="ps-4 py-3">المادة الدراسية</th>
        <th class="text-center">الحصص الأسبوعية</th> {{-- العمود الجديد --}}
        <th class="text-center">أعمال السنة</th>
        <th class="text-center">الامتحان النهائي</th>
        <th class="text-center">المجموع</th>
    </tr>
                </thead>
                {{-- أضف خانة الإدخال في الـ Tbody --}}
                    <tbody>
                        @foreach($subjects as $subject)
                        @php 
                            $dist = $subject->getGradeDistribution(); 
                            // جلب عدد الحصص من المودل (الذي يبحث في جدول الإعدادات تلقائياً)
                            $weeklyClasses = $subject->getClassesCount(); 
                        @endphp
                        <tr>
                            <input type="hidden" name="subject_id[]" value="{{ $subject->id }}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $subject->name }}</div>
                                <span class="badge bg-light text-muted border py-0 fw-normal" style="font-size: 10px;">{{ $subject->grade->name ?? '' }}</span>
                            </td>
                            <td class="text-center">
                                <input type="number" name="weekly_classes[]" class="form-control form-control-sm text-center mx-auto shadow-sm" 
                                    style="width: 70px; border-color: #e3e6f0;" value="{{ $weeklyClasses }}" required min="1">
                            </td>
                            <td class="text-center">
                                <input type="number" name="works_score[]" class="form-control form-control-sm text-center mx-auto shadow-sm border-primary border-opacity-10" 
                                    style="width: 80px;" value="{{ $dist['works'] }}" required min="0">
                            </td>
                            <td class="text-center">
                                <input type="number" name="final_score[]" class="form-control form-control-sm text-center mx-auto shadow-sm border-success border-opacity-10" 
                                    style="width: 80px;" value="{{ $dist['final'] }}" required min="0">
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary border px-3 py-2 fw-bold">
                                    {{ $dist['total'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-center py-4 border-top">
            <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow fw-bold">
                <i class="fas fa-save me-2"></i> اعتماد وإرسال التوزيع
            </button>
        </div>
    </div>
</form>
@else
<div class="card shadow border-0 py-5">
    <div class="card-body text-center">
        <i class="fas fa-search fa-3x text-light mb-3"></i>
        <h5 class="text-muted">لا توجد مواد مربوطة بهذا الصف حالياً.</h5>
    </div>
</div>
@endif

@endsection