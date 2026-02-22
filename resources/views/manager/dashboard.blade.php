@extends('layouts.manager')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">لوحة التحكم 📊</h2>
            <p class="mb-0 opacity-75">أهلاً بك، المدير {{ Auth::user()->name }} 👋. إليك ملخص سريع لما يحدث اليوم.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-chart-line fa-4x opacity-25"></i>
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
                        <div class="h3 mb-0 fw-bold text-dark">{{ $stats['students_count'] }}</div>
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
                        <div class="h3 mb-0 fw-bold text-dark">{{ $stats['teachers_count'] }}</div>
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
                        <div class="h3 mb-0 fw-bold text-dark">{{ $stats['classes_count'] }}</div>
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
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">سجل النشاطات</div>
                    {{-- عرض عدد العمليات المسجلة --}}
                    <div class="h3 mb-0 fw-bold text-dark">{{ $stats['logs_count'] ?? 0 }}</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('manager.system_logs') }}">
                        <i class="fas fa-history fa-2x text-warning opacity-25"></i>
                    </a>
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
                    
                    {{-- زر تعيين الأدمن --}}
                    <a href="{{ route('manager.create_admin') }}" class="btn btn-outline-primary btn-lg flex-grow-1 shadow-sm">
                        <i class="fas fa-user-shield mb-2 d-block fs-3"></i>
                        تعيين مسؤول دراسة
                    </a>

                    {{-- زر استعراض المعلمين --}}
                    <a href="{{ route('manager.teachers.index') }}" class="btn btn-outline-success btn-lg flex-grow-1 shadow-sm">
                        <i class="fas fa-chalkboard-teacher mb-2 d-block fs-3"></i>
                        سجل المعلمين
                    </a>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary">📅 حالة النظام</h6>
            </div>
            <div class="card-body text-center py-5">
                <i class="fas fa-server fa-3x text-muted mb-3 opacity-25"></i>
                <p class="text-muted small">النظام يعمل بكفاءة. لا توجد تنبيهات.</p>
            </div>
        </div>
    </div>
</div>

@endsection