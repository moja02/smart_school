@extends('layouts.admin')
@section('title', 'سجل طلبات الاستئذان')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-history text-secondary me-2"></i> سجل طلبات الاستئذان</h3>
        <a href="{{ route('admin.leaves') }}" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة لطلبات اليوم
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.leaves.history') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">العذر</label>
                        <input type="text" name="reason" class="form-control" value="{{ request('reason') }}" placeholder="ابحث بجزء من العذر أو النوع...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">اسم الطالب</label>
                        <input type="text" name="student" class="form-control" value="{{ request('student') }}" placeholder="اسم الطالب...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الصف / الفصل</label>
                        <select name="class_id" class="form-select">
                            <option value="">جميع الفصول</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->grade->name ?? 'بدون صف' }} - {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($leaves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>الصف/الفصل</th>
                            <th>مقدم الطلب</th>
                            <th>النوع</th>
                            <th>التاريخ</th>
                            <th>السبب</th>
                            <th>الحالة النهائية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td>{{ $leave->student->user->name ?? 'غير متوفر' }}</td>
                            <td>
                                @if($leave->student->schoolClass)
                                    <span class="badge bg-info text-dark">
                                        {{ $leave->student->schoolClass->grade->name ?? '' }} - {{ $leave->student->schoolClass->name }}
                                    </span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td>{{ $leave->parent->name ?? 'غير متوفر' }}</td>
                            <td>
                                @if($leave->type == 'early_leave') خروج مبكر
                                @elseif($leave->type == 'absence') غياب يوم كامل
                                @elseif($leave->type == 'late') تأخر صباحي
                                @else {{ $leave->type }}
                                @endif
                            </td>
                            <td>{{ $leave->leave_date }}</td>
                            <td>{{ Str::limit($leave->reason, 50) }}</td>
                            <td>
                                @if($leave->status == 'pending')
                                    <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                @elseif($leave->status == 'approved')
                                    <span class="badge bg-success">مقبول</span>
                                @elseif($leave->status == 'rejected')
                                    <span class="badge bg-danger">مرفوض</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 d-flex justify-content-center">
                {{ $leaves->appends(request()->query())->links() }}
            </div>
            
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open fa-4x mb-3 text-light"></i>
                <h5>لا توجد طلبات سابقة مطابقة للبحث</h5>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
