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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">قائمة الطلاب والرسوم</h6>
            <form class="form-inline" method="GET" action="{{ route('admin.fees.index') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control bg-light border-0 small" placeholder="ابحث عن طالب..." value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i> بحث
                        </button>
                    </div>
                </div>
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
});
</script>
@endsection
