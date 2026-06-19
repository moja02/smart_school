@extends('layouts.admin')
@section('title', 'إدارة المصروفات')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-file-invoice-dollar text-danger me-2"></i> إدارة المصروفات (Expenses)</h3>
        <button class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fas fa-plus"></i> تسجيل مصروف جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>تاريخ المصروف</th>
                        <th>العنوان</th>
                        <th>التصنيف</th>
                        <th>المبلغ</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $index => $expense)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}</td>
                        <td class="fw-bold">{{ $expense->title }}</td>
                        <td>
                            @if($expense->category == 'salaries') <span class="badge bg-primary">رواتب وأجور</span>
                            @elseif($expense->category == 'utilities') <span class="badge bg-info">فواتير ومنافع</span>
                            @elseif($expense->category == 'maintenance') <span class="badge bg-warning text-dark">صيانة وإصلاح</span>
                            @else <span class="badge bg-secondary">مصروفات أخرى</span>
                            @endif
                        </td>
                        <td class="fw-bold text-danger">{{ number_format($expense->amount, 2) }} <small class="text-muted">د.ل</small></td>
                        <td>{{ $expense->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">لا توجد مصروفات مسجلة حتى الآن.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Expense -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">تسجيل مصروف مالي جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.accounting.expenses.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان المصروف</label>
                        <input type="text" name="title" class="form-control" required placeholder="مثال: فاتورة كهرباء شهر 10">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">التصنيف</label>
                            <select name="category" class="form-select" required>
                                <option value="salaries">رواتب وأجور (Salaries)</option>
                                <option value="utilities">فواتير ومنافع (Utilities)</option>
                                <option value="maintenance">صيانة وإصلاح (Maintenance)</option>
                                <option value="other">مصروفات أخرى (Other)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المبلغ (د.ل)</label>
                            <input type="number" name="amount" step="0.01" class="form-control" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ الدفع</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات (اختياري)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-danger px-4">تسجيل المصروف</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
