@extends('layouts.parent')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-clipboard-check me-2"></i> ملخص الانضباط والغياب</h2>
        <a href="{{ route('parent.children') }}" class="btn btn-secondary rounded-pill px-4">الرجوع</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark">الحضور والغياب العام</h5>
                </div>
                <div class="card-body p-4 text-center">
                    @if($presentCount > 0 || $absentCount > 0)
                        <canvas id="disciplineChart" style="max-height: 250px;"></canvas>
                        <div class="mt-4 d-flex justify-content-around">
                            <div class="text-success">
                                <h3 class="fw-bold mb-0">{{ $presentCount }}</h3>
                                <small>أيام الحضور</small>
                            </div>
                            <div class="text-danger">
                                <h3 class="fw-bold mb-0">{{ $absentCount }}</h3>
                                <small>أيام الغياب</small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mt-5">لم يتم تسجيل أي حالة حضور أو غياب لهذا الطالب بعد.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0 text-dark">سجل الاستئذانات والمخالفات</h5>
                </div>
                <div class="card-body p-4">
                    @if(count($leaves) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>النوع / السبب</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaves as $leave)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($leave->date)->format('Y-m-d') }}</td>
                                        <td>{{ $leave->reason }}</td>
                                        <td>
                                            @if($leave->status == 'approved')
                                                <span class="badge bg-success">مقبول</span>
                                            @elseif($leave->status == 'rejected')
                                                <span class="badge bg-danger">مرفوض</span>
                                            @else
                                                <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success text-center mt-5">
                            <i class="fas fa-check-circle fs-1 mb-3"></i><br>
                            لا توجد أي استئذانات أو ملاحظات سلبية مسجلة على الطالب. سجله نظيف تماماً!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($presentCount > 0 || $absentCount > 0)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('disciplineChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['أيام الحضور', 'أيام الغياب'],
                datasets: [{
                    data: [{{ $presentCount }}, {{ $absentCount }}],
                    backgroundColor: [
                        '#198754', // success
                        '#dc3545'  // danger
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endif
@endsection
