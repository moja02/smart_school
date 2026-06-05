@extends('layouts.admin')

@section('content')

{{-- الترويسة العلوية الداكنة --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white" style="border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white"><i class="fas fa-edit text-warning me-2"></i> تعديل درجات التقييمات</h2>
            <p class="mb-0 text-white-50">تحكم كامل في تعديل ورصد أي درجة لأي طالب في أي فصل.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-clipboard-check fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

{{-- عرض رسالة النجاح --}}
@if(session('success'))
    <div class="alert alert-success fw-bold shadow-sm rounded-pill px-4 py-3 mb-4 border-0">
        <i class="fas fa-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
    </div>
@endif

{{-- 1. الفلاتر التفاعلية --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-filter me-2 text-primary"></i> تحديد الفصل والمادة والسميستر</h6>
    </div>
    <div class="card-body p-4 bg-light">
        <form action="{{ route('admin.marks.edit') }}" method="GET" id="filterForm">
            <div class="row g-3">
                
                {{-- اختيار الفصل --}}
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2 small">1. اختر الفصل الدراسي:</label>
                    <select name="class_id" class="form-select shadow-sm border-0" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- حدد الفصل --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- اختيار المادة --}}
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2 small">2. اختر المادة:</label>
                    <select name="subject_id" class="form-select shadow-sm border-0" onchange="document.getElementById('filterForm').submit()" {{ !$selectedClass ? 'disabled' : '' }}>
                        <option value="">-- حدد المادة --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ $selectedSubject == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- اختيار السميستر (الجديد) --}}
                <div class="col-md-4">
                    <label class="fw-bold text-dark mb-2 small">3. عرض التقييمات حسب:</label>
                    <select name="semester" class="form-select shadow-sm border-0" onchange="document.getElementById('filterForm').submit()" {{ !$selectedSubject ? 'disabled' : '' }}>
                        <option value="">-- المجموع (كل التقييمات) --</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>السميستر الأول</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>السميستر الثاني</option>
                    </select>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- 2. جدول رصد وتعديل الدرجات الشامل --}}
@if($selectedClass && $selectedSubject)
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-users text-success me-2"></i> قائمة الطلاب والتقييمات</h6>
        <span class="badge bg-primary px-3 py-2 rounded-pill">عدد التقييمات المعروضة: {{ $assessments->count() }}</span>
    </div>
    
    <div class="card-body p-0">
        @if($students->count() > 0 && $assessments->count() > 0)
            <form action="{{ route('admin.marks.update') }}" method="POST">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 text-center">
                        <thead class="table-dark text-white small">
                            <tr>
                                <th class="py-3 text-start ps-4" style="min-width: 250px;">اسم الطالب</th>
                                {{-- توليد أعمدة ديناميكية لكل تقييم --}}
                                @foreach($assessments as $assessment)
                                    <th class="py-3" style="width: 160px;">
                                        {{ $assessment->title ?? $assessment->name }}<br>
                                        <small class="text-warning fw-normal">(من {{ $assessment->max_score ?? $assessment->full_mark ?? 100 }})</small><br>
                                        {{-- عرض شارة توضح السميستر --}}
                                        <span class="badge bg-light text-dark mt-1" style="font-size: 10px;">
                                            {{ $assessment->semester == 2 ? 'السميستر الثاني' : 'السميستر الأول' }}
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    {{-- اسم الطالب --}}
                                    <td class="fw-bold text-dark text-start ps-4 bg-light">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-dark text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 35px; height: 35px; font-size: 14px;">
                                                {{ mb_substr($student->user->name ?? 'ط', 0, 1) }}
                                            </div>
                                            {{ $student->user->name ?? 'طالب غير معروف' }}
                                        </div>
                                    </td>

                                    {{-- حقول إدخال الدرجات لكل تقييم --}}
                                    @foreach($assessments as $assessment)
                                        @php 
                                            // البحث عن درجة هذا الطالب في هذا التقييم تحديداً
                                            $studentMarks = $existingMarks->get($student->id);
                                            $markRecord = $studentMarks ? $studentMarks->get($assessment->id) : null;
                                            $currentScore = $markRecord ? ($markRecord->score ?? $markRecord->marks ?? $markRecord->mark) : null;
                                        @endphp
                                        <td>
                                            <input type="number" 
                                                   step="0.5" 
                                                   name="marks[{{ $student->id }}][{{ $assessment->id }}]" 
                                                   class="form-control text-center fw-bold shadow-sm {{ $currentScore !== null ? 'border-success bg-success bg-opacity-10' : '' }}" 
                                                   value="{{ $currentScore }}" 
                                                   placeholder="--" 
                                                   min="0" 
                                                   max="{{ $assessment->max_score ?? $assessment->full_mark ?? 100 }}">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light p-4 text-end" style="border-radius: 0 0 1rem 1rem;">
                    <button type="submit" class="btn btn-dark btn-lg rounded-pill px-5 shadow fw-bold hover-scale">
                        <i class="fas fa-save me-2 text-warning"></i> حفظ جميع الدرجات
                    </button>
                </div>
            </form>
        @elseif($assessments->count() == 0)
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-warning mb-3"></i>
                <h6 class="fw-bold text-dark">لا توجد تقييمات مضافة بناءً على التصفية.</h6>
                <p class="text-muted small">يرجى من المعلم إضافة تقييمات ليتمكن من الرصد.</p>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted opacity-25 mb-3"></i>
                <h6 class="fw-bold text-muted">لا يوجد طلاب مسجلين في هذا الفصل حالياً.</h6>
            </div>
        @endif
    </div>
</div>
@endif

<style>
    .hover-scale:hover { transform: scale(1.02); transition: 0.2s ease-in-out; }
    /* إخفاء أسهم الأرقام لتبدو أنظف */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
</style>

@endsection