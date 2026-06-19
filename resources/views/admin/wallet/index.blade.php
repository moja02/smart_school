@extends('layouts.admin')
@section('title', 'إدارة المحفظة والمقصف')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-wallet text-primary me-2"></i> إدارة المحفظة والمقصف المدرسي</h3>
        <div>
            @if(auth()->user()->role === 'manager' || auth()->user()->hasPermissionTo('manage_wallet_charge'))
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#topupModal">
                <i class="fas fa-arrow-up"></i> إيداع رصيد
            </button>
            @endif

            @if(auth()->user()->role === 'manager' || auth()->user()->hasPermissionTo('manage_wallet_sell'))
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#purchaseModal">
                <i class="fas fa-shopping-cart"></i> تسجيل مشتريات
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- جدول عرض الأرصدة -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">أرصدة الطلاب الحالية</h5>
            <form class="form-inline" method="GET" action="{{ route('admin.wallet') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-sm border-0 bg-light" placeholder="ابحث باسم الطالب..." value="{{ request('search') }}">
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>الفصل الدراسي</th>
                            <th>الرصيد المتاح</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td class="fw-bold">{{ $student->name }}</td>
                            <td>{{ $student->studentProfile->schoolClass->name ?? 'غير مسكن' }}</td>
                            <td>
                                @php $bal = $student->studentProfile->wallet_balance ?? 0; @endphp
                                <span class="badge rounded-pill {{ $bal > 0 ? 'bg-success' : 'bg-secondary' }} fs-6">
                                    {{ number_format($bal, 2) }} دينار
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Topup -->
<div class="modal fade" id="topupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">إيداع رصيد (شحن المحفظة)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.wallet.topup') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اختر الطالب</label>
                        <select name="student_id" class="form-select select2-topup" style="width: 100%" required>
                            <option value="">-- اختر --</option>
                            @foreach($students as $s)
                                @if($s->studentProfile)
                                <option value="{{ $s->studentProfile->id }}">{{ $s->name }} ({{ $s->studentProfile->schoolClass->name ?? '' }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ (دينار)</label>
                        <input type="number" step="0.5" min="1" name="amount" class="form-control" required placeholder="مثال: 10">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تنفيذ الإيداع</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Purchase -->
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">تسجيل مشتريات مقصف (خصم رصيد)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.wallet.purchase') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اختر الطالب</label>
                        <select name="student_id" class="form-select select2-purchase" style="width: 100%" required>
                            <option value="">-- اختر --</option>
                            @foreach($students as $s)
                                @if($s->studentProfile)
                                <option value="{{ $s->studentProfile->id }}">{{ $s->name }} (رصيد: {{ number_format($s->studentProfile->wallet_balance ?? 0, 2) }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مبلغ المشتريات (دينار)</label>
                        <input type="number" step="0.5" min="0.5" name="amount" class="form-control" required placeholder="مثال: 2.5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تفاصيل المشتريات (اختياري)</label>
                        <input type="text" name="description" class="form-control" value="مشتريات من المقصف المدرسي">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرقم السري للطالب (PIN) - إذا كان مفعلاً</label>
                        <input type="password" name="wallet_pin" class="form-control" maxlength="4" placeholder="••••">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تنفيذ الخصم</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- jQuery is required for Select2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-topup').select2({
            dropdownParent: $('#topupModal'),
            placeholder: "ابحث باسم الطالب...",
            allowClear: true
        });
        $('.select2-purchase').select2({
            dropdownParent: $('#purchaseModal'),
            placeholder: "ابحث باسم الطالب...",
            allowClear: true
        });
    });
</script>
@endsection
