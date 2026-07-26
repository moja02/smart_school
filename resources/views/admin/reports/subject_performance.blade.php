@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-book-open me-2"></i> الأداء الأكاديمي للمواد</h2>
        <a href="{{ route('admin.reports.advanced') }}" class="btn btn-secondary">الرجوع</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            @if(count($performance) > 0)
                <canvas id="subjectChart" style="max-height: 400px;"></canvas>
            @else
                <div class="alert alert-info text-center">لا توجد بيانات درجات كافية لعرض هذا التقرير.</div>
            @endif
        </div>
    </div>

    {{-- تقرير تفصيلي بأداء الدروس --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white pt-4 pb-0 px-4 border-0">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ul me-2 text-primary"></i> تقرير استيعاب الدروس (بناءً على الاختبارات القصيرة)</h5>
            <p class="text-muted small mt-1">يعرض هذا الجدول نسبة إجابات الطلاب الصحيحة لكل درس لقياس مدى استيعابهم للمحتوى.</p>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            @if(isset($lessonPerformance) && count($lessonPerformance) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>المادة</th>
                                <th>اسم الدرس</th>
                                <th class="text-center">إجمالي الإجابات</th>
                                <th style="width: 30%;">نسبة الاستيعاب (الإجابات الصحيحة)</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lessonPerformance as $lesson)
                            <tr>
                                <td class="fw-bold text-secondary">{{ $lesson->subject_name }}</td>
                                <td class="fw-bold text-dark">{{ $lesson->lesson_name }}</td>
                                <td class="text-center">{{ $lesson->total_possible }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 fw-bold">{{ $lesson->percentage }}%</span>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar {{ $lesson->percentage >= 75 ? 'bg-success' : ($lesson->percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $lesson->percentage }}%" 
                                                 aria-valuenow="{{ $lesson->percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($lesson->percentage >= 75)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> استيعاب ممتاز</span>
                                    @elseif($lesson->percentage >= 50)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i> استيعاب متوسط</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> يحتاج مراجعة (ضعيف)</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-secondary text-center">لا توجد بيانات لاختبارات قصيرة مرتبطة بدروس حالياً.</div>
            @endif
        </div>
    </div>
</div>

@if(count($performance) > 0)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('subjectChart').getContext('2d');
        const performanceData = @json($performance);
        
        const labels = performanceData.map(item => item.name);
        const data = performanceData.map(item => item.average);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'متوسط الدرجات',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    });
</script>
@endif
@endsection
