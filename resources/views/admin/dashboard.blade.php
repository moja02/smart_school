@extends('layouts.admin')

@section('content')

{{-- 1. الترويسة الرئيسية --}}
<div class="card page-header-card mb-4 shadow-lg border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 15px;">
    <div class="card-body d-flex justify-content-between align-items-center p-4">
        <div>
            <h2 class="fw-bold mb-1 text-white">مركز البيانات والإحصائيات 📊</h2>
            <p class="mb-0 opacity-75 fs-5">
                أهلاً بك، المدير <strong>{{ Auth::user()->name }}</strong> 👋. إليك ملخص الأداء المالي والأكاديمي.
            </p>
            
            <div class="mt-3">
                <span class="badge bg-white text-dark shadow-sm px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> {{ date('Y-m-d') }}
                </span>
            </div>
        </div>
        
        <div class="d-none d-md-block">
            <i class="fas fa-chart-line fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>
<div >
    
</div>

{{-- 2. بطاقات الإحصائيات --}}
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

{{-- 3. القسم السفلي: الوصول السريع + التقويم --}}
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

    {{-- الإحصائيات --}}
    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="m-0 fw-bold text-secondary">📊 توزيع المستخدمين في النظام</h6>
            </div>
            <div class="card-body py-3 d-flex flex-column justify-content-center align-items-center">
                <canvas id="usersRoleChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- 4. القسم المالي: الأرباح والمصروفات --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center rounded-top-4">
                <h5 class="m-0 fw-bold" style="color: #2a5298;"><i class="fas fa-wallet me-2"></i> المؤشرات المالية (الأرباح مقابل المصروفات)</h5>
            </div>
            <div class="card-body">
                <div id="financialChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); }
    .rounded-4 { border-radius: 1rem !important; }
    .rounded-top-4 { border-top-left-radius: 1rem !important; border-top-right-radius: 1rem !important; }
</style>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Demographic Pie Chart (ApexCharts) ---
        var demographicOptions = {
            series: [{{ $usersDemographics['الطلاب'] ?? 0 }}, {{ $usersDemographics['المعلمون'] ?? 0 }}, {{ $usersDemographics['أولياء الأمور'] ?? 0 }}],
            chart: {
                type: 'donut',
                height: 280,
                fontFamily: 'Tahoma, sans-serif'
            },
            labels: ['الطلاب', 'المعلمون', 'أولياء الأمور'],
            colors: ['#4e73df', '#1cc88a', '#36b9cc'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { show: true },
                            value: { show: true }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: false
            }
        };

        var demographicChart = new ApexCharts(document.querySelector("#usersRoleChart"), demographicOptions);
        demographicChart.render();


        // --- 2. Financial Area Chart (ApexCharts) ---
        var financialOptions = {
            series: [{
                name: 'الإيرادات (الرسوم)',
                data: {!! json_encode($revenueData ?? []) !!}
            }, {
                name: 'المصروفات',
                data: {!! json_encode($expenseData ?? []) !!}
            }],
            chart: {
                height: 350,
                type: 'area',
                fontFamily: 'Tahoma, sans-serif',
                toolbar: {
                    show: true
                }
            },
            colors: ['#1cc88a', '#e74a3b'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: {!! json_encode($months ?? []) !!},
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return value + " د.ل";
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " د.ل"
                    }
                }
            }
        };

        var financialChart = new ApexCharts(document.querySelector("#financialChart"), financialOptions);
        financialChart.render();
    });
</script>
@endsection