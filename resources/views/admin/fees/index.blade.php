@extends('layouts.admin')

@section('title', 'إدارة الرسوم الدراسية')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">إدارة الرسوم الدراسية</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow border-0 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.fees.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">البحث بالإسم أو البريد:</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="ابحث عن طالب..." value="{{ $search ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">الصف الدراسي:</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-university text-primary"></i></span>
                            <select name="grade_id" class="form-select border-start-0">
                                <option value="">-- كل الصفوف --</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">حالة السداد:</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-money-bill-wave text-success"></i></span>
                            <select name="payment_status" class="form-select border-start-0">
                                <option value="">-- الكل --</option>
                                <option value="full" {{ request('payment_status') == 'full' ? 'selected' : '' }}>سدد بالكامل</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>تسديد جزئي</option>
                                <option value="none" {{ request('payment_status') == 'none' ? 'selected' : '' }}>لم يسدد</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1 shadow-sm fw-bold">
                            <i class="fas fa-filter me-1"></i> تصفية
                        </button>
                        <a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary shadow-sm" title="إعادة ضبط">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">قائمة الطلاب والرسوم</h6>
            <form action="{{ route('admin.fees.notify') }}" method="POST" class="m-0" id="notifyForm">
                @csrf
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="grade_id" value="{{ request('grade_id') }}">
                <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                <button type="button" id="notifyBtn" class="btn btn-warning btn-sm shadow-sm fw-bold">
                    <i class="fas fa-bell me-1"></i> إرسال تنبيه للأولياء (المعروضين)
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>الطالب</th>
                            <th>الفصل</th>
                            <th>ولي الأمر</th>
                            <th>إجمالي الرسوم</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>حالة الحساب</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>{{ $student->user->name ?? 'غير محدد' }}</td>
                            <td>{{ $student->schoolClass->name ?? 'غير محدد' }}</td>
                            <td>{{ $student->parent?->user?->name ?? 'لا يوجد' }}</td>
                            <td>
                                <form action="{{ route('admin.fees.update_total', $student->id) }}" method="POST" class="d-flex justify-content-center align-items-center">
                                    @csrf
                                    <input type="number" name="total_fees" value="{{ $student->total_fees }}" class="form-control form-control-sm w-50 mr-2" step="0.01">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save"></i></button>
                                </form>
                            </td>
                            <td class="text-success font-weight-bold">{{ number_format($student->paid_fees, 2) }}</td>
                            <td class="text-danger font-weight-bold">{{ number_format($student->remaining_fees, 2) }}</td>
                            <td>
                                @if($student->user->is_banned)
                                    <span class="badge badge-danger">معطل</span>
                                @else
                                    <span class="badge badge-success">نشط</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#payModal{{ $student->id }}">
                                    <i class="fas fa-money-bill-wave"></i> دفع
                                </button>
                                
                                <form action="{{ route('admin.fees.suspend', $student->id) }}" method="POST" class="d-inline suspend-form">
                                    @csrf
                                    <button type="button" class="btn btn-sm {{ $student->user->is_banned ? 'btn-success' : 'btn-danger' }} mb-1 btn-suspend" data-action="{{ $student->user->is_banned ? 'تفعيل' : 'تعطيل' }}">
                                        <i class="fas {{ $student->user->is_banned ? 'fa-check' : 'fa-ban' }}"></i> 
                                        {{ $student->user->is_banned ? 'تفعيل' : 'تعطيل' }}
                                    </button>
                                </form>

                                @if($student->feePayments->count() > 0)
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-info dropdown-toggle mb-1" type="button" id="receiptsDropdown{{ $student->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-file-invoice"></i> إيصالات
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="receiptsDropdown{{ $student->id }}">
                                            @foreach($student->feePayments as $payment)
                                                <a class="dropdown-item" href="{{ route('admin.fees.receipt', $payment->id) }}" target="_blank">
                                                    إيصال: {{ $payment->receipt_number }} ({{ $payment->amount }})
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <!-- Pay Modal -->
                        <div class="modal fade" id="payModal{{ $student->id }}" tabindex="-1" role="dialog" aria-labelledby="payModalLabel{{ $student->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('admin.fees.pay', $student->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="payModalLabel{{ $student->id }}">تسجيل دفعة جديدة للطالب: {{ $student->user->name }}</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>المبلغ المراد دفعه</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" min="1" max="{{ $student->remaining_fees > 0 ? $student->remaining_fees : '' }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>تاريخ الدفع</label>
                                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>ملاحظات (اختياري)</label>
                                                <textarea name="notes" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-primary">حفظ وإصدار إيصال</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8">لا يوجد طلاب.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $students->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Suspend buttons logic
    const suspendButtons = document.querySelectorAll('.btn-suspend');
    suspendButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const actionType = this.getAttribute('data-action');
            
            Swal.fire({
                title: 'تأكيد العملية',
                text: `هل أنت متأكد من ${actionType} حالة حساب هذا الطالب وولي الأمر؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: actionType === 'تفعيل' ? '#198754' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، ' + actionType,
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Notify button logic
    const notifyBtn = document.getElementById('notifyBtn');
    if (notifyBtn) {
        notifyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('notifyForm');
            
            Swal.fire({
                title: 'إرسال تنبيه جماعي',
                text: 'هل أنت متأكد من إرسال رسالة تنبيه بخصوص الرسوم إلى جميع أولياء أمور الطلاب المعروضين في الجدول حالياً؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، أرسل التنبيهات',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>
@endsection
