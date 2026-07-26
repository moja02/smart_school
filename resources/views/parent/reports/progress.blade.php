@extends('layouts.parent')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-chart-line me-2"></i> المنحنى الأكاديمي للطالب</h2>
        <a href="{{ route('parent.children') }}" class="btn btn-secondary rounded-pill px-4">الرجوع</a>
    </div>

    <!-- بطاقة معلومات الطالب -->
    <div class="card border-0 shadow-sm mb-4 bg-light" style="border-radius: 15px;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">{{ $student->name }}</h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-graduation-cap me-1"></i> {{ $student->studentProfile->schoolClass->grade->name ?? 'الصف' }} 
                    | <i class="fas fa-chalkboard me-1"></i> {{ $student->studentProfile->schoolClass->name ?? 'الشعبة' }}
                </p>
            </div>
            <div class="display-4 text-primary opacity-25">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0">مستوى التقدم الأكاديمي</h5>
        </div>
        <div class="card-body p-4">
            @if(count($scores) > 0)
                <canvas id="progressChart" style="max-height: 400px;"></canvas>
            @else
                <div class="alert alert-info text-center">لا توجد بيانات درجات كافية لعرض هذا المنحنى.</div>
            @endif
        </div>
    </div>

    {{-- قسم الدروس القوية --}}
    @if(isset($strongLessons) && count($strongLessons) > 0)
    <div class="card shadow-sm border-0 border-top border-success border-3 mt-4" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0 text-success"><i class="fas fa-star me-2"></i> نقاط القوة (أداء ممتاز)</h5>
            <p class="text-muted small mt-1">الدروس والتقييمات التي حصل فيها الطالب على 80% أو أكثر.</p>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @foreach($strongLessons as $strong)
                <div class="col-md-4">
                    <div class="p-3 border border-success rounded-3 bg-light position-relative h-100">
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">{{ $strong->percentage }}%</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $strong->lesson_name }}</h6>
                        <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i> {{ $strong->subject_name }}</p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $strong->percentage }}%" aria-valuenow="{{ $strong->percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-success mt-2 d-block">سجل {{ $strong->score }} من أصل {{ $strong->total }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- قسم الدروس المتوسطة --}}
    @if(isset($averageLessons) && count($averageLessons) > 0)
    <div class="card shadow-sm border-0 border-top border-warning border-3 mt-4" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0 text-warning"><i class="fas fa-minus-circle me-2"></i> أداء متوسط (يمكن تحسينه)</h5>
            <p class="text-muted small mt-1">الدروس والتقييمات التي حصل فيها الطالب على نسبة بين 50% و 79%.</p>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @foreach($averageLessons as $average)
                <div class="col-md-4">
                    <div class="p-3 border border-warning rounded-3 bg-light position-relative h-100">
                        <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">{{ $average->percentage }}%</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $average->lesson_name }}</h6>
                        <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i> {{ $average->subject_name }}</p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $average->percentage }}%" aria-valuenow="{{ $average->percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-warning text-dark mt-2 d-block">سجل {{ $average->score }} من أصل {{ $average->total }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- قسم الدروس الضعيفة --}}
    @if(isset($weakLessons) && count($weakLessons) > 0)
    <div class="card shadow-sm border-0 border-top border-danger border-3 mt-4" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i> دروس تتطلب المتابعة (نقاط الضعف)</h5>
            <p class="text-muted small mt-1">الدروس والتقييمات التي حصل فيها الطالب على نسبة أقل من 50%.</p>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @foreach($weakLessons as $weak)
                <div class="col-md-4">
                    <div class="p-3 border border-danger rounded-3 bg-light position-relative h-100">
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">{{ $weak->percentage }}%</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $weak->lesson_name }}</h6>
                        <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i> {{ $weak->subject_name }}</p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $weak->percentage }}%" aria-valuenow="{{ $weak->percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-danger mt-2 d-block">سجل {{ $weak->score }} من أصل {{ $weak->total }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    
    @if(count($strongLessons ?? []) == 0 && count($averageLessons ?? []) == 0 && count($weakLessons ?? []) == 0)
    <div class="alert alert-info mt-4 shadow-sm border-0 text-center" style="border-radius: 15px;">
        <i class="fas fa-info-circle fs-4 mb-2"></i>
        <h5 class="alert-heading fw-bold">لا توجد بيانات تفصيلية</h5>
        <p class="mb-0">لم يتم رصد أي درجات تقييم أو اختبارات تجريبية للطالب حتى الآن.</p>
    </div>
    @endif
</div>

@if(count($scores) > 0)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('progressChart');
        const ctx = canvas.getContext('2d');
        const scoresData = @json($scores);
        
        const labels = scoresData.map(item => item.subject_name);
        const data = scoresData.map(item => item.total_score);

        // إنشاء تدرج لوني جذاب للأعمدة (Gradient)
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.3)');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'نسبة الإنجاز والتفوق %',
                    data: data,
                    backgroundColor: gradient,
                    borderColor: '#36a2eb',
                    borderWidth: 2,
                    borderRadius: 6, // زوايا دائرية للأعمدة
                    barPercentage: 0.6 // عرض العمود
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Tahoma, sans-serif',
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { family: 'Tahoma', size: 14 },
                        bodyFont: { family: 'Tahoma', size: 13, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'المستوى: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                        },
                        ticks: {
                            stepSize: 20,
                            font: { family: 'Tahoma', size: 12 },
                            color: '#666',
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            font: { family: 'Tahoma', size: 13, weight: 'bold' },
                            color: '#444'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    });
</script>
@endif
@endsection
