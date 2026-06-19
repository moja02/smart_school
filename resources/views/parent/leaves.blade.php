@extends('layouts.parent')
@section('title', 'طلبات الاستئذان والغياب')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>طلبات الاستئذان والغياب</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
            <i class="fas fa-plus"></i> تقديم طلب جديد
        </button>
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
                            <th>الابن</th>
                            <th>نوع الطلب</th>
                            <th>تاريخ الطلب</th>
                            <th>السبب</th>
                            <th>حالة الطلب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td>{{ $leave->student->user->name ?? 'غير معروف' }}</td>
                            <td>
                                @if($leave->type == 'absence')
                                    <span class="badge bg-secondary">غياب يوم كامل</span>
                                @else
                                    <span class="badge bg-warning text-dark">خروج مبكر</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($leave->leave_date)->format('Y-m-d') }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>
                                @if($leave->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> قيد المراجعة</span>
                                @elseif($leave->status == 'approved')
                                    <span class="badge bg-success"><i class="fas fa-check"></i> مقبول</span>
                                @elseif($leave->status == 'rejected')
                                    <span class="badge bg-danger"><i class="fas fa-times"></i> مرفوض</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center p-5 text-muted bg-light rounded">
                <i class="fas fa-envelope-open-text fs-1 mb-3 text-secondary"></i>
                <h5>لا توجد طلبات سابقة</h5>
                <p>يمكنك تقديم طلب غياب أو خروج مبكر لأي من أبنائك عبر الزر أعلاه.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Add Leave -->
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addLeaveModalLabel">تقديم طلب استئذان جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('parent.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اختر الابن</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- اختر --</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}">{{ $child->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع الطلب</label>
                        <select name="type" class="form-select" required>
                            <option value="absence">غياب عن يوم دراسي</option>
                            <option value="early_leave">استئذان خروج مبكر</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التاريخ المقصود</label>
                        <input type="date" name="leave_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">عذر الغياب / سبب الخروج</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="مثال: مراجعة طبيب، ظرف عائلي طارئ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرسال الطلب</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
