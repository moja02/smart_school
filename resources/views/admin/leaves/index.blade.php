@extends('layouts.admin')
@section('title', 'إدارة طلبات الاستئذان')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-envelope-open-text text-primary me-2"></i> طلبات الاستئذان والغياب</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($leaves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>مقدم الطلب (ولي الأمر)</th>
                            <th>النوع</th>
                            <th>التاريخ</th>
                            <th>السبب</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td class="fw-bold">{{ $leave->student->user->name ?? 'غير معروف' }}</td>
                            <td>{{ $leave->parent->name ?? 'غير معروف' }}</td>
                            <td>
                                @if($leave->type == 'absence')
                                    <span class="badge bg-secondary">غياب يوم</span>
                                @else
                                    <span class="badge bg-warning text-dark">خروج مبكر</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($leave->leave_date)->format('Y-m-d') }}</td>
                            <td class="text-muted"><small>{{ Str::limit($leave->reason, 40) }}</small></td>
                            <td>
                                @if($leave->status == 'pending')
                                    <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                @elseif($leave->status == 'approved')
                                    <span class="badge bg-success">مقبول</span>
                                @else
                                    <span class="badge bg-danger">مرفوض</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->status == 'pending')
                                <form action="{{ route('admin.leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success" title="قبول"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('admin.leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-sm btn-danger" title="رفض"><i class="fas fa-times"></i></button>
                                </form>
                                @else
                                    <span class="text-muted">تم الرد</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center p-5 text-muted bg-light rounded">
                <i class="fas fa-inbox fs-1 mb-3 text-secondary"></i>
                <h5>لا توجد طلبات استئذان حالياً</h5>
                <p>جميع الطلبات المقدمة من أولياء الأمور ستظهر هنا لمراجعتها.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
