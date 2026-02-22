@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">لوحة التحكم 📊</h2>
            <p class="mb-0 opacity-75">أهلاً بك، المدير {{ Auth::user()->name }} 👋. إليك ملخص سريع لما يحدث اليوم.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-school fa-4x opacity-25"></i>
        </div>
    </div>
</div>

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
                        <i class="fas fa-user-graduate fa-2x text-gray-300 text-primary opacity-25"></i>
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
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300 text-success opacity-25"></i>
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
                        <i class="fas fa-layer-group fa-2x text-gray-300 text-info opacity-25"></i>
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
                        {{--  يمكنك استبدال الرقم بمتغير حقيقي لاحقاً --}}
                        <div class="h3 mb-0 fw-bold text-dark">{{ \App\Models\Subject::count() }}</div> 
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-bolt text-warning me-2"></i> إجراءات سريعة</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary btn-lg flex-grow-1 shadow-sm">
                        <i class="fas fa-user-plus mb-2 d-block fs-3"></i>
                        تسجيل مستخدم
                    </a>
                    <a href="{{ route('admin.subjects.grade_settings') }}" class="btn btn-outline-warning btn-lg flex-grow-1 shadow-sm text-dark">
                        <i class="fas fa-percentage mb-2 d-block fs-3"></i>
                        توزيع الدرجات
                    </a>
                    <a href="{{ route('admin.classes') }}" class="btn btn-outline-success btn-lg flex-grow-1 shadow-sm">
                        <i class="fas fa-chalkboard mb-2 d-block fs-3"></i>
                        إدارة الفصول
                    </a>
                    <a href="{{ route('admin.assign') }}" class="btn btn-outline-info btn-lg flex-grow-1 shadow-sm text-dark">
                        <i class="fas fa-link mb-2 d-block fs-3"></i>
                        توزيع المواد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary">📅 التقويم الدراسي</h6>
            </div>
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3 opacity-25"></i>
                <p class="text-muted small">لا توجد أحداث قادمة مسجلة في التقويم.</p>
            </div>
        </div>
    </div>
</div>

@endsection