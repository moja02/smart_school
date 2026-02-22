@extends('layouts.teacher')

@section('content')
<div class="container py-4">

    {{-- 1. الترويسة الرئيسية (النمط الداكن الموحد) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                {{-- أيقونة تعبيرية --}}
                <div class="me-3 d-none d-md-block">
                    <i class="fas fa-layer-group fa-3x opacity-25 text-warning"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-1 text-white">
                        فصولي الدراسية
                    </h2>
                    <p class="mb-0 opacity-75">
                        إدارة المواد، رصد الدرجات، ومتابعة الطلاب للفصول المسندة إليك.
                    </p>
                </div>
            </div>
            
            {{-- عداد المواد --}}
            <div>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-book me-1"></i> {{ count($subjects) }} مواد
                </span>
            </div>
        </div>
    </div>

    {{-- 2. شبكة الكروت (Cards Grid) --}}
    <div class="row g-4">
        @forelse($subjects as $index => $subject)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow border-0 hover-scale overflow-hidden">
                {{-- شريط ملون جمالي أعلى الكرت --}}
                <div class="bg-primary opacity-75" style="height: 5px;"></div>

                <div class="card-body text-center p-4">
                    {{-- أيقونة الحرف الأول --}}
                    <div class="avatar-lg bg-light text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm border" style="width: 70px; height: 70px;">
                        <span class="h2 fw-bold mb-0">{{ mb_substr($subject->subject_name, 0, 1) }}</span>
                    </div>

                    <h4 class="card-title fw-bold mb-1 text-dark">{{ $subject->subject_name }}</h4>
                    <span class="badge bg-light text-secondary border mb-3">{{ $subject->grade_name }}</span>

                    <div class="d-flex justify-content-center gap-3 text-muted small mb-4">
                        <span><i class="fas fa-chalkboard me-1 text-warning"></i> شعبة ({{ $subject->class_section }})</span>
                        <span class="text-light">|</span>
                        <span>
                            <i class="fas fa-users me-1 text-info"></i> 
                            {{-- ✅ عداد الطلاب الصحيح باستخدام الجدول المباشر --}}
                            {{ \DB::table('student_profiles')->where('class_id', $subject->class_id)->count() }} طالب
                        </span>
                    </div>
                    
                    {{-- زر الدخول للفصل --}}
                    <a href="{{ route('teacher.class.show', ['subject_id' => $subject->subject_id, 'class_id' => $subject->class_id]) }}" class="btn btn-outline-primary rounded-pill px-4 w-100 fw-bold">
                        دخول الفصل <i class="fas fa-arrow-left ms-2"></i>
                    </a>
                </div>

                <div class="card-footer bg-white border-top-0 text-center pb-3 pt-0">
                    {{-- رابط الغياب --}}
                    <a href="{{ route('teacher.attendance.index', ['section_id' => $subject->class_id]) }}" class="btn btn-link text-secondary text-decoration-none btn-sm">
                        <i class="fas fa-clipboard-list me-1"></i> سجل الحضور والغياب
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="opacity-25 mb-3"><i class="fas fa-chalkboard-teacher fa-4x text-muted"></i></div>
                    <h4 class="text-muted fw-bold">لم يتم إسناد أي فصول لك حتى الآن.</h4>
                    <p class="text-muted small">يرجى التواصل مع مدير النظام لتوزيع المواد الدراسية.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<style>
    /* تأثير التحويم الجمالي */
    .hover-scale { transition: all 0.3s ease; }
    .hover-scale:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .avatar-lg { font-size: 1.5rem; }
</style>
@endsection