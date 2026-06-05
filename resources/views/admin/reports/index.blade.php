@extends('layouts.admin')

@section('content')
<div class="card page-header-card mb-4 shadow border-0 no-print">
    <div class="card-body">
        <h2 class="fw-bold mb-1 text-white">مركز التقارير والإحصائيات 📊</h2>
        <p class="text-white-50 mb-0">لوحة التحكم لاستخراج كشوفات الدرجات وتقارير الأوائل والنواقص.</p>
    </div>
</div>

{{-- 1. بطاقات اختيار نوع التقرير --}}
<div class="row g-4 mb-5 no-print">
    {{-- كرت أوائل الصفوف --}}
    <div class="col-md-4">
        <a href="{{ route('admin.reports.index', ['type' => 'top_students']) }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition {{ request('type') == 'top_students' ? 'border-start border-primary border-4' : '' }}">
                <div class="card-body text-center p-4">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-trophy fa-2x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">أوائل الصفوف</h5>
                    <p class="small text-muted">عرض وطباعة قائمة العشرة الأوائل.</p>
                </div>
            </div>
        </a>
    </div>

    {{-- كرت شهادات الطلاب --}}
    <div class="col-md-4">
        <a href="{{ route('admin.reports.index', ['type' => 'certificates']) }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition {{ request('type') == 'certificates' ? 'border-start border-success border-4' : '' }}">
                <div class="card-body text-center p-4">
                    <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-file-certificate fa-2x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">شهادات الطلاب</h5>
                    <p class="small text-muted">طباعة كشف درجات تفصيلي لكل طالب.</p>
                </div>
            </div>
        </a>
    </div>

    {{-- كرت نواقص الإسناد (الجديد) --}}
    <div class="col-md-4">
        <a href="{{ route('admin.reports.index', ['type' => 'missing_teachers']) }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition {{ request('type') == 'missing_teachers' ? 'border-start border-danger border-4' : '' }}">
                <div class="card-body text-center p-4">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">نواقص الإسناد</h5>
                    <p class="small text-muted">اكتشاف الفصول والمواد بدون أساتذة.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<hr class="mb-5 no-print">

{{-- 2. عرض المحتوى بناءً على النوع المختار --}}

{{-- أولاً: قسم تقرير الأوائل --}}
@if(request('type') == 'top_students')
    <div class="card shadow border-0 mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i> تصفية نتائج الأوائل</h5>
            
            <div class="d-flex gap-2 align-items-center">
                {{-- زر طباعة القائمة --}}
                @if(request('grade_id') && isset($topStudents) && $topStudents->count() > 0)
                    <a href="{{ route('admin.reports.print', ['grade_id' => request('grade_id')]) }}" target="_blank" class="btn btn-dark shadow-sm px-4">
                        <i class="fas fa-print me-2"></i> طباعة التقرير
                    </a>
                @endif

                {{-- فورم اختيار الصف --}}
                <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex gap-2 m-0">
                    <input type="hidden" name="type" value="top_students">
                    <select name="grade_id" class="form-select shadow-sm" onchange="this.form.submit()" style="min-width: 200px;">
                        <option value="">-- اختر الصف الدراسي --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        
        @if(isset($topStudents) && $topStudents->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light">
                            <tr>
                                <th>الترتيب</th>
                                <th class="text-end">اسم الطالب</th>
                                <th>الشعبة</th>
                                <th>المجموع</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topStudents as $index => $student)
                            <tr>
                                <td>
                                    <span class="badge bg-dark rounded-pill">{{ $index + 1 }}</span>
                                </td>
                                <td class="text-end fw-bold">{{ $student->name }}</td>
                                <td>{{ $student->studentProfile->schoolClass->section ?? 'غير محدد' }}</td>
                                <td class="text-primary fw-bold">{{ number_format($student->total_final_score, 1) }}</td>
                                <td>
                                    <span class="fw-bold">{{ number_format($student->percentage, 1) }}%</span>
                                    <div class="progress mx-auto" style="height: 4px; width: 60px;">
                                        <div class="progress-bar bg-success" style="width: {{ $student->percentage }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request('grade_id'))
            <div class="card-body text-center py-5">
                <p class="text-muted">لا توجد نتائج مرصودة لهذا الصف حالياً.</p>
            </div>
        @else
            <div class="card-body text-center py-5">
                <p class="text-muted">يرجى اختيار الصف لعرض النتائج.</p>
            </div>
        @endif
    </div>

{{-- ثانياً: قسم شهادات الطلاب  --}}
@elseif(request('type') == 'certificates')
    <div class="card shadow border-0 mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-success"></i> قائمة الطلاب (استخراج الشهادات)</h5>
            
            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex gap-2 m-0">
                    <input type="hidden" name="type" value="certificates">
                    <select name="grade_id" class="form-select shadow-sm" onchange="this.form.submit()" style="min-width: 200px;">
                        <option value="">-- اختر الصف الدراسي --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if(isset($studentsList) && $studentsList->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">اسم الطالب</th>
                                <th>الشعبة</th>
                                <th>حالة الدرجات</th>
                                <th class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentsList as $student)
                            <tr>
                                <td class="fw-bold ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-light text-primary me-2 rounded-circle text-center fw-bold" style="width:35px; height:35px; line-height:35px;">
                                            {{ mb_substr($student->name, 0, 1) }}
                                        </div>
                                        {{ $student->name }}
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $student->studentProfile->schoolClass->section ?? '-' }}</span></td>
                                <td>
                                    {{-- فحص وجود درجات --}}
                                   @php $hasGrades = \DB::table('student_scores')->where('student_id', $student->id)->exists(); @endphp
                                    @if($hasGrades)
                                        <span class="badge bg-success bg-opacity-10 text-success">مرصودة</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">غير متوفرة</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.reports.print_certificate', $student->id) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        <i class="fas fa-file-alt me-1"></i> طباعة الشهادة
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request('grade_id'))
            <div class="card-body text-center py-5">
                <p class="text-muted">لا يوجد طلاب مسجلين في هذا الصف.</p>
            </div>
        @else
            <div class="card-body text-center py-5">
                <p class="text-muted">يرجى اختيار الصف لعرض قائمة الطلاب.</p>
            </div>
        @endif
    </div>

{{-- ثالثاً: قسم تقرير النواقص (الجديد) --}}
@elseif(request('type') == 'missing_teachers')
    <div class="card shadow border-0 mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2 text-danger"></i> تقرير النواقص (مواد بدون أساتذة)</h5>
            
            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex gap-2 m-0">
                    <input type="hidden" name="type" value="missing_teachers">
                    <select name="grade_id" class="form-select shadow-sm border-danger" onchange="this.form.submit()" style="min-width: 200px;">
                        <option value="">-- اختر الصف الدراسي --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if(isset($missingAssignments) && $missingAssignments->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th width="20%">الفصل / الشعبة</th>
                                <th width="60%" class="text-start ps-4">المواد التي تحتاج إلى إسناد</th>
                                <th width="20%">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($missingAssignments as $item)
                            <tr>
                                <td class="fw-bold text-dark fs-6">{{ $item->class->name ?? $item->class->section }}</td>
                                <td class="text-start ps-4">
                                    @foreach($item->missing_subjects as $subject)
                                        <span class="badge bg-warning text-dark border border-warning fs-6 mb-1 me-1 shadow-sm">
                                            <i class="fas fa-book-open me-1"></i> {{ $subject->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    {{-- توجيه الإداري لصفحة الإسناد مع تحديد الصف تلقائياً --}}
                                    <a href="{{ route('admin.assign') }}?grade_id={{ request('grade_id') }}" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3">
                                        <i class="fas fa-link me-1"></i> إسناد الآن
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request('grade_id'))
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3 opacity-50"></i>
                <h4 class="text-success fw-bold">الوضع ممتاز!</h4>
                <p class="text-muted">تم إسناد جميع المواد لمعلمين في جميع فصول هذا الصف.</p>
            </div>
        @else
            <div class="card-body text-center py-5">
                <p class="text-muted">يرجى اختيار الصف لعرض النواقص والفجوات.</p>
            </div>
        @endif
    </div>
@endif

@endsection

@section('styles')
<style>
    .transition { transition: all 0.3s ease; }
    .hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
</style>
@endsection