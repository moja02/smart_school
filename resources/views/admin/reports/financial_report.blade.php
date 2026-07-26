@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-chart-line me-2"></i> التقرير المالي الشامل</h2>
        <a href="{{ route('admin.reports.advanced') }}" class="btn btn-secondary">الرجوع</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-money-bill-wave me-2"></i> إجمالي الرسوم المحصلة</h5>
                    <h3 class="display-5 fw-bold mt-3">{{ number_format($totalFees, 2) }} د.ل</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-info text-white h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-wallet me-2"></i> إجمالي شحن المحافظ (المقصف)</h5>
                    <h3 class="display-5 fw-bold mt-3">{{ number_format($totalWallet, 2) }} د.ل</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i> إجمالي المصروفات</h5>
                    <h3 class="display-5 fw-bold mt-3">{{ number_format($totalExpenses, 2) }} د.ل</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            @if($totalFees > 0 || $totalWallet > 0 || $totalExpenses > 0)
                <canvas id="financialChart" style="max-height: 400px;"></canvas>
            @else
                <div class="alert alert-info text-center">لا توجد بيانات مالية للعرض.</div>
            @endif
        </div>
    </div>
</div>

@if($totalFees > 0 || $totalWallet > 0 || $totalExpenses > 0)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('financialChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['الرسوم المحصلة', 'شحن المحافظ', 'المصروفات'],
                datasets: [{
                    label: 'المبلغ (د.ل)',
                    data: [{{ $totalFees }}, {{ $totalWallet }}, {{ $totalExpenses }}],
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)', // primary
                        'rgba(13, 202, 240, 0.7)', // info
                        'rgba(220, 53, 69, 0.7)'   // danger
                    ],
                    borderColor: [
                        'rgb(13, 110, 253)',
                        'rgb(13, 202, 240)',
                        'rgb(220, 53, 69)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endif
@endsection
