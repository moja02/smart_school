@extends('layouts.admin')
@section('title', 'التقرير المالي (الأرباح والخسائر)')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-chart-line text-success me-2"></i> التقرير المالي الشامل لسنة {{ $currentYear }}</h3>
        <div>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> طباعة التقرير</button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-success bg-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">إجمالي الإيرادات (Revenues)</div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ number_format($totalRevenues, 2) }} <small class="text-muted fs-6">د.ل</small></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-danger bg-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">إجمالي المصروفات (Expenses)</div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ number_format($totalExpenses, 2) }} <small class="text-muted fs-6">د.ل</small></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-primary bg-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">صافي الربح / الخسارة (Net Profit)</div>
                            <div class="h3 mb-0 fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($netProfit, 2) }} <small class="text-muted fs-6">د.ل</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-balance-scale fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-secondary">رسم بياني: الإيرادات مقابل المصروفات شهرياً ({{ $currentYear }})</h6>
        </div>
        <div class="card-body">
            <canvas id="financialChart" style="height: 350px;"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'الإيرادات',
                        data: {!! json_encode($chartData['revenues']) !!},
                        backgroundColor: 'rgba(28, 200, 138, 0.7)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'المصروفات',
                        data: {!! json_encode($chartData['expenses']) !!},
                        backgroundColor: 'rgba(231, 74, 59, 0.7)',
                        borderColor: 'rgba(231, 74, 59, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return value + ' د.ل';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: { family: 'Tahoma' }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
