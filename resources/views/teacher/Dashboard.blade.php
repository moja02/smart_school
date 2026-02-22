@extends('layouts.teacher')

@section('content')

@php
    // جلب بيانات المدرسة وحالة الرصد
    $school = \App\Models\School::find(auth()->user()->school_id);
    $teacherId = auth()->id();
    
    // ✅ تصحيح الاستعلامات بناءً على اسم الجدول الصحيح: teacher_subject_section
    
    // 1. عدد المواد المختلفة التي يدرسها المعلم (نستخدم distinct لعدم تكرار المادة إذا كان يدرسها لأكثر من فصل)
    $subjectsCount = \DB::table('teacher_subject_section')
                        ->where('teacher_id', $teacherId)
                        ->distinct('subject_id')
                        ->count('subject_id');
    
    // 2. عدد الفصول (الشعب) التي يدرسها المعلم
    $classesCount = \DB::table('teacher_subject_section')
                        ->where('teacher_id', $teacherId)
                        ->distinct('section_id')
                        ->count('section_id');
@endphp

<div class="container py-4">

    {{-- 1. الترويسة الرئيسية (نفس تصميم الأدمن الفخم: داكن وخط أبيض) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-white">لوحة المعلم 👨‍🏫</h2>
                <p class="mb-0 opacity-75">
                    أهلاً بك، الأستاذ <strong>{{ Auth::user()->name }}</strong> 👋.
                </p>

                {{-- حالة نظام الرصد --}}
                <div class="mt-3">
                    @if($school->grading_locked)
                        <span class="badge bg-danger p-2 shadow-sm">
                            <i class="fas fa-lock me-1"></i> الرصد مغلق من الإدارة
                        </span>
                    @else
                        <span class="badge bg-success p-2 shadow-sm text-dark">
                            <i class="fas fa-unlock me-1"></i> الرصد متاح حالياً
                        </span>
                    @endif
                    <span class="text-white-50 ms-2 small"><i class="fas fa-calendar me-1"></i> {{ date('Y-m-d') }}</span>
                </div>
            </div>
            
            {{-- الأيقونة الخلفية --}}
            <div class="d-none d-md-block">
                <i class="fas fa-chalkboard-teacher fa-4x opacity-25 text-white"></i>
            </div>
        </div>
    </div>

    {{-- 2. بطاقات الإحصائيات --}}
    <div class="row g-4 mb-4">
        
        {{-- كرت عدد الفصول --}}
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">فصولي الدراسية</div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $classesCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- كرت عدد المواد --}}
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">المواد المسندة</div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $subjectsCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- كرت الرسائل (يمكن تفعيله لاحقاً) --}}
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">الرسائل الجديدة</div>
                            {{-- مثال لعدد الرسائل غير المقروءة --}}
                            @php
                                $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', 0)->count();
                            @endphp
                            <div class="h3 mb-0 fw-bold text-dark">{{ $unreadMessages }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. القسم السفلي: الوصول السريع والجدول --}}
    <div class="row">
        {{-- الوصول السريع --}}
        <div class="col-lg-8">
            <div class="card shadow border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-rocket me-2"></i> الوصول السريع</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- زر الفصول (الأهم للمعلم) --}}
                        <div class="col-md-6">
                            <a href="{{ route('teacher.classes') }}" class="btn btn-outline-primary w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none">
                                <i class="fas fa-chalkboard fa-2x"></i>
                                <span class="fw-bold fs-5">فصولي الدراسية</span>
                                <small class="text-muted">رصد الدرجات، الغياب، والتقييمات</small>
                            </a>
                        </div>

                        {{-- زر الملف الشخصي --}}
                        <div class="col-md-6">
                            <a class="btn btn-outline-primary w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                <span class="fw-bold fs-5">الملف الشخصي</span>
                                <small class="text-muted">تعديل البيانات وكلمة المرور</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- الجدول الدراسي المصغر --}}
        <div class="col-lg-4">
            <div class="card shadow border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-secondary">📅 جدول حصص اليوم</h6>
                </div>
                <div class="card-body text-center py-5 d-flex flex-column justify-content-center">
                    <i class="fas fa-calendar-day fa-4x text-light mb-3"></i>
                    <p class="text-muted small">لا توجد حصص مسجلة لهذا اليوم.</p>
                    {{-- رابط للجدول الكامل إذا كان متوفراً --}}
                    <a href="#" class="btn btn-sm btn-link text-decoration-none">عرض الجدول الكامل</a>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); }
</style>

@endsection