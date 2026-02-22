@extends('layouts.admin')

@section('content')

@php
    $school = \App\Models\School::find(auth()->user()->school_id);
@endphp

{{-- 1. الترويسة الرئيسية (نفس هيكلية الملف الأصلي لكن بخط أبيض وخلفية داكنة) --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            {{-- النص أبيض كما طلبت --}}
            <h2 class="fw-bold mb-1 text-white">لوحة التحكم 📊</h2>
            <p class="mb-0 opacity-75">
                أهلاً بك، المدير <strong>{{ Auth::user()->name }}</strong> 👋. إليك ملخص سريع لما يحدث اليوم.
            </p>
            
            {{-- زر التحكم في الرصد (تم دمجه هنا للحفاظ على الهيكلية) --}}
            <div class="mt-3">
                <form action="{{ route('admin.grading.toggle') }}" method="POST" class="d-inline-block">
                    @csrf
                    @if($school->grading_locked)
                        <button type="submit" class="btn btn-danger btn-sm shadow-sm fw-bold px-3">
                            <i class="fas fa-lock me-1"></i> الرصد مغلق (اضغط لفتحه)
                        </button>
                    @else
                        <button type="submit" class="btn btn-success btn-sm shadow-sm fw-bold px-3">
                            <i class="fas fa-unlock me-1"></i> الرصد متاح (اضغط لإغلاقه)
                        </button>
                    @endif
                </form>
                <span class="text-white-50 ms-2 small"><i class="fas fa-calendar me-1"></i> {{ date('Y-m-d') }}</span>
            </div>
        </div>
        
        {{-- الأيقونة الكبيرة (من الملف الأصلي) --}}
        <div class="d-none d-md-block">
            <i class="fas fa-school fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

{{-- 2. بطاقات الإحصائيات (نفس الملف الأصلي) --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">إجمالي الطلاب</div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ $totalStudents ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">عدد المعلمين</div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ $totalTeachers ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">الفصول الدراسية</div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ $classes ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-layer-group fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">المواد الدراسية</div>
                        <div class="h3 mb-0 fw-bold text-dark">{{ \App\Models\Subject::count() }}</div> 
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. القسم السفلي: الوصول السريع (الجديد) + التقويم --}}
<div class="row">
    {{-- الوصول السريع والعمليات --}}
    <div class="col-lg-8">
        <div class="card shadow border-0 mb-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-rocket me-2"></i> الوصول السريع والعمليات</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('admin.users.create') }}?role=student" class="btn btn-outline-primary w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none">
                            <i class="fas fa-user-graduate fa-2x"></i>
                            <span class="fw-bold">إضافة طالب</span>
                        </a>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('admin.users.create') }}?role=teacher" class="btn btn-outline-success w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                            <span class="fw-bold">إضافة معلم</span>
                        </a>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('admin.classes') }}" class="btn btn-outline-info w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none">
                            <i class="fas fa-layer-group fa-2x"></i>
                            <span class="fw-bold">إدارة الفصول</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <a href="{{ route('admin.subjects') }}" class="btn btn-outline-warning text-dark w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none">
                            <i class="fas fa-book fa-2x"></i>
                            <span class="fw-bold">المواد الدراسية</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <a href="{{ route('admin.settings.structure') }}" class="btn btn-outline-secondary w-100 h-100 py-4 shadow-sm d-flex flex-column align-items-center justify-content-center gap-2 hover-scale text-decoration-none">
                            <i class="fas fa-cogs fa-2x"></i>
                            <span class="fw-bold">إعدادات المدرسة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- التقويم الدراسي --}}
    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary">📅 التقويم الدراسي</h6>
            </div>
            <div class="card-body text-center py-5 d-flex flex-column justify-content-center">
                <i class="fas fa-calendar-check fa-4x text-light mb-3"></i>
                <p class="text-muted small">لا توجد أحداث قادمة مسجلة في التقويم.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); }
    /* لضمان أن خلفية الهيدر داكنة ليظهر الخط الأبيض */
    .bg-dark { background-color: #212529 !important; }
</style>

@endsection