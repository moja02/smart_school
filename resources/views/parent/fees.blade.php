@extends('layouts.parent')
@section('title', 'الرسوم والدفعات')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">كشف الرسوم والدفعات المالية</h3>
    @if($children->count() == 0)
        <div class="alert alert-warning">لا يوجد أبناء مرتبطين بحسابك.</div>
    @endif
    @foreach($children as $child)
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> {{ $child->user->name ?? 'غير معروف' }}</h5>
        </div>
        <div class="card-body">
            <div class="row text-center mb-4">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-muted">إجمالي الرسوم المطلوبة</h6>
                        <h4 class="text-primary mb-0">{{ number_format($child->total_fees, 2) }} دينار</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-muted">إجمالي المدفوع</h6>
                        <h4 class="text-success mb-0">{{ number_format($child->paid_fees, 2) }} دينار</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-muted">المتبقي للدفع</h6>
                        <h4 class="text-danger mb-0">{{ number_format($child->remaining_fees, 2) }} دينار</h4>
                    </div>
                </div>
            </div>
            <h6 class="mb-3 text-secondary border-bottom pb-2">سجل الدفعات (الإيصالات)</h6>
            @if(count($child->feePayments) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>رقم الإيصال</th>
                            <th>المبلغ المدفوع</th>
                            <th>تاريخ الدفع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($child->feePayments as $payment)
                        <tr>
                            <td><span class="badge bg-secondary fs-6">{{ $payment->receipt_number }}</span></td>
                            <td class="text-success fw-bold">{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted p-3 bg-light rounded">
                <i class="fas fa-receipt fs-1 mb-2 text-secondary"></i>
                <p class="mb-0">لا توجد أي دفعات مسجلة حتى الآن.</p>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
