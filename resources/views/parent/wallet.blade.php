@extends('layouts.parent')
@section('title', 'المحفظة الرقمية')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">المحفظة الرقمية والمقصف المدرسي</h3>
    @if($children->count() == 0)
        <div class="alert alert-warning">لا يوجد أبناء مرتبطين بحسابك.</div>
    @endif
    @foreach($children as $child)
    <div class="card mb-4 shadow-sm border-0 border-top border-primary border-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h5 class="mb-1"><i class="fas fa-wallet text-primary me-2"></i> {{ $child->user->name ?? 'غير معروف' }}</h5>
                    <small class="text-muted">مراقبة مصروف المقصف</small>
                </div>
                <div class="text-end bg-light p-3 rounded">
                    <h6 class="text-muted mb-1">الرصيد المتاح</h6>
                    <h3 class="mb-0 {{ $child->wallet_balance > 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($child->wallet_balance, 2) }} دينار
                    </h3>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded border border-secondary border-opacity-25">
                        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-lock me-1"></i> إعداد الرقم السري للمقصف</h6>
                        <form action="{{ route('parent.wallet.pin') }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $child->id }}">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" name="wallet_pin" class="form-control" maxlength="4" placeholder="•••• (4 أرقام)" value="{{ $child->wallet_pin }}" required>
                                <button type="submit" class="btn btn-outline-primary">حفظ / تعديل</button>
                            </div>
                        </form>
                        <small class="text-muted d-block mt-2">لحماية رصيد الطالب، يمكنك وضع رقم سري (PIN) من 4 أرقام سيُطلب منه عند الشراء من المقصف. (استخدم 4 أرقام فقط)</small>
                    </div>
                </div>
            </div>

            <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-history me-1"></i> سجل العمليات الأخيرة</h6>
            @if(isset($transactions[$child->id]) && $transactions[$child->id]->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>نوع العملية</th>
                            <th>التفاصيل</th>
                            <th>المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions[$child->id] as $tx)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d h:i A') }}</td>
                            <td>
                                @if($tx->type == 'topup')
                                    <span class="badge bg-success"><i class="fas fa-arrow-up"></i> إيداع / شحن</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-arrow-down"></i> خصم / شراء</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $tx->description }}</td>
                            <td class="fw-bold {{ $tx->type == 'topup' ? 'text-success' : 'text-danger' }}">
                                {{ $tx->type == 'topup' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center p-3 text-muted">
                <p class="mb-0">لا توجد أي عمليات في المحفظة حتى الآن.</p>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
