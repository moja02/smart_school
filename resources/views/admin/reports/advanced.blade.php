@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i> مركز التقارير المتقدمة</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-warning mb-3"><i class="fas fa-trophy"></i></div>
                    <h5 class="fw-bold">أوائل الطلبة</h5>
                    <p class="text-muted">عرض ترتيب الطلاب بناءً على المجموع الكلي للدرجات.</p>
                    <a href="{{ route('admin.reports.top_students') }}" class="btn btn-outline-primary rounded-pill px-4">عرض التقرير</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-info mb-3"><i class="fas fa-book-open"></i></div>
                    <h5 class="fw-bold">الأداء الأكاديمي للمواد</h5>
                    <p class="text-muted">تحليل متوسط درجات الطلاب في مختلف المواد الدراسية.</p>
                    <a href="{{ route('admin.reports.subject_performance') }}" class="btn btn-outline-primary rounded-pill px-4">عرض التقرير</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-success mb-3"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h5 class="fw-bold">أداء المعلمين</h5>
                    <p class="text-muted">قياس كفاءة المعلمين بناءً على متوسط درجات طلابهم.</p>
                    <a href="{{ route('admin.reports.teachers_performance') }}" class="btn btn-outline-primary rounded-pill px-4">عرض التقرير</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-danger mb-3"><i class="fas fa-user-clock"></i></div>
                    <h5 class="fw-bold">إحصائيات الغياب</h5>
                    <p class="text-muted">مقارنة بين معدلات الحضور والغياب للطلاب بصرياً.</p>
                    <a href="{{ route('admin.reports.attendance_analytics') }}" class="btn btn-outline-primary rounded-pill px-4">عرض التقرير</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-success mb-3"><i class="fas fa-chart-line"></i></div>
                    <h5 class="fw-bold">التقرير المالي</h5>
                    <p class="text-muted">مقارنة الإيرادات (رسوم + مقصف) بالمصروفات.</p>
                    <a href="{{ route('admin.reports.financial_report') }}" class="btn btn-outline-primary rounded-pill px-4">عرض التقرير</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
