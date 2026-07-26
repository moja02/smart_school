@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-user-clock me-2"></i> إحصائيات الغياب المتقدمة</h2>
        <a href="{{ route('admin.reports.advanced') }}" class="btn btn-secondary">الرجوع</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    @if($presentCount > 0 || $absentCount > 0)
                        <canvas id="attendanceChart" style="max-height: 400px;"></canvas>
                    @else
                        <div class="alert alert-info text-center">لا توجد سجلات حضور وغياب لعرضها.</div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-success text-white">
                        <div class="card-body text-center p-4">
                            <h4>إجمالي أيام الحضور</h4>
                            <h2 class="display-4 fw-bold">{{ $presentCount }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-danger text-white">
                        <div class="card-body text-center p-4">
                            <h4>إجمالي أيام الغياب</h4>
                            <h2 class="display-4 fw-bold">{{ $absentCount }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($presentCount > 0 || $absentCount > 0)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['حاضر', 'غائب'],
                datasets: [{
                    data: [{{ $presentCount }}, {{ $absentCount }}],
                    backgroundColor: [
                        '#198754', // success
                        '#dc3545'  // danger
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>
@endif
@endsection
