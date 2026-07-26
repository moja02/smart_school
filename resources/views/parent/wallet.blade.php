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
            
            <div class="row mb-4 g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border border-secondary border-opacity-25 h-100">
                        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-lock me-1"></i> إعداد الرقم السري للمقصف</h6>
                        <form action="{{ route('parent.wallet.pin') }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $child->id }}">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" name="wallet_pin" class="form-control" maxlength="4" placeholder="••••" value="{{ $child->wallet_pin }}" required>
                                <button type="submit" class="btn btn-outline-primary">حفظ</button>
                            </div>
                        </form>
                        <small class="text-muted d-block mt-2">سيُطلب عند الشراء من المقصف.</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border border-secondary border-opacity-25 h-100">
                        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-shield-alt me-1"></i> الحد الأعلى للمصروف اليومي</h6>
                        <form action="{{ route('parent.wallet.limit') }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $child->id }}">
                            <div class="input-group">
                                <span class="input-group-text bg-white">د.ل</span>
                                <input type="number" name="daily_limit" class="form-control" step="0.5" min="0" placeholder="بدون سقف" value="{{ $child->daily_limit }}">
                                <button type="submit" class="btn btn-outline-primary">حفظ</button>
                            </div>
                        </form>
                        <small class="text-muted d-block mt-2">ضع 0 أو اتركه فارغاً لإلغاء السقف.</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border border-secondary border-opacity-25 h-100 text-center">
                        <h6 class="text-secondary fw-bold mb-3 text-start"><i class="fas fa-exchange-alt me-1"></i> تحويل رصيد داخلي</h6>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#transferModal-{{ $child->id }}">
                            <i class="fas fa-paper-plane me-1"></i> تحويل رصيد لأحد الأبناء
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal التحويل --}}
            <div class="modal fade" id="transferModal-{{ $child->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title">تحويل رصيد من ({{ $child->user->name ?? 'غير معروف' }})</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('parent.wallet.transfer') }}" method="POST">
                            @csrf
                            <input type="hidden" name="from_student_id" value="{{ $child->id }}">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">تحويل إلى (الابن المستفيد):</label>
                                    <select name="to_student_id" class="form-select" required>
                                        <option value="" disabled selected>اختر الابن...</option>
                                        @foreach($children as $sibling)
                                            @if($sibling->id != $child->id)
                                                <option value="{{ $sibling->id }}">{{ $sibling->user->name ?? 'غير معروف' }} (الرصيد: {{ $sibling->wallet_balance ?? 0 }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المبلغ (د.ل):</label>
                                    <input type="number" name="amount" class="form-control" step="0.5" min="1" max="{{ $child->wallet_balance }}" required placeholder="أدخل المبلغ المراد تحويله">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">تأكيد التحويل</button>
                            </div>
                        </form>
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
