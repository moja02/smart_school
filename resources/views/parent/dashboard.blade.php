@extends('layouts.parent')

@section('content')

{{-- الترويسة العلوية باللون الداكن المعتاد --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white d-print-none" style="border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">مرحباً، {{ Auth::user()->name }} 👋</h2>
            <p class="mb-0 text-white-50">اختر أحد أبنائك لعرض وطباعة كشف درجاته الرسمي.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-users fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

@if($children->count() > 0)
    {{-- 1. بطاقات الأبناء (تعمل كأزرار تبويب Tabs) - مخفية عند الطباعة --}}
    <ul class="nav nav-pills row g-4 mb-4 d-print-none" id="childrenTabs" role="tablist">
        @foreach($children as $index => $child)
            <li class="nav-item col-md-6 col-lg-4" role="presentation">
                <button class="nav-link w-100 p-0 bg-transparent border-0 {{ $index === 0 ? 'active' : '' }}" id="tab-btn-{{ $child->id }}" data-bs-toggle="pill" data-bs-target="#tab-content-{{ $child->id }}" type="button" role="tab">
                    <div class="card shadow-sm border-0 child-card hover-scale text-start h-100" style="border-radius: 1rem;">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="avatar-sm bg-dark text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 60px; height: 60px;">
                                {{ mb_substr($child->user->name ?? 'ط', 0, 1) }}
                            </div>
                            <div>
                                <h5 class="m-0 fw-bold text-dark mb-1">{{ $child->user->name ?? 'غير معروف' }}</h5>
                                <span class="badge bg-light text-dark border"><i class="fas fa-chalkboard me-1"></i> {{ $child->schoolClass->name ?? 'غير محدد' }}</span>
                            </div>
                        </div>
                    </div>
                </button>
            </li>
        @endforeach
    </ul>

    {{-- 2. محتوى تفاصيل الأبناء (الدرجات والشهادات) --}}
    <div class="tab-content" id="childrenTabsContent">
        @foreach($children as $index => $child)
            @php $details = $childrenDetails[$child->user_id]; @endphp
            
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="tab-content-{{ $child->id }}" role="tabpanel">
                
                {{-- لوحة القيادة الذكية (Premium Dashboard UI) --}}
                <div class="row g-4 mt-2 d-print-none">
                    {{-- بطاقة 1: النسبة الأكاديمية --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 opacity-75">المستوى الأكاديمي العام</h6>
                                    <i class="fas fa-graduation-cap fa-2x opacity-50"></i>
                                </div>
                                <h2 class="display-5 fw-bold mb-0">{{ $details->percentage }}<span class="fs-4 opacity-50">%</span></h2>
                                <p class="mb-0 mt-2 small opacity-75">بناءً على التقييمات المرصودة حتى الآن</p>
                            </div>
                            <div class="position-absolute" style="right: -20px; bottom: -20px; opacity: 0.1;">
                                <i class="fas fa-chart-line fa-10x"></i>
                            </div>
                        </div>
                    </div>

                    {{-- بطاقة 2: رصيد المحفظة --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 opacity-75">رصيد المقصف (المحفظة)</h6>
                                    <i class="fas fa-wallet fa-2x opacity-50"></i>
                                </div>
                                <h2 class="display-5 fw-bold mb-0">{{ number_format($details->wallet ?? 0, 2) }}<span class="fs-4 opacity-50"> د.ل</span></h2>
                                <p class="mb-0 mt-2 small opacity-75">الرصيد المتاح للاستخدام في مقصف المدرسة</p>
                            </div>
                            <div class="position-absolute" style="right: -20px; bottom: -20px; opacity: 0.1;">
                                <i class="fas fa-coins fa-10x"></i>
                            </div>
                        </div>
                    </div>

                    {{-- بطاقة 3: إحصائية الحضور والغياب --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 opacity-75">سجل الانضباط والمواظبة</h6>
                                    <i class="fas fa-user-clock fa-2x opacity-50"></i>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <div class="text-center">
                                        <div class="fs-3 fw-bold">{{ $details->attendance['present'] ?? 0 }}</div>
                                        <small class="opacity-75">حضور</small>
                                    </div>
                                    <div class="text-center">
                                        <div class="fs-3 fw-bold">{{ $details->attendance['late'] ?? 0 }}</div>
                                        <small class="opacity-75">تأخير</small>
                                    </div>
                                    <div class="text-center">
                                        <div class="fs-3 fw-bold">{{ $details->attendance['absent'] ?? 0 }}</div>
                                        <small class="opacity-75">غياب</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- قسم الرسوم البيانية --}}
                <div class="row g-4 mt-2 d-print-none">
                    {{-- الرسم البياني الشبكي للمواد --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-dark"><i class="fas fa-chart-column text-primary me-2"></i> تحليل الأداء الأكاديمي (نقاط القوة والضعف)</h5>
                            </div>
                            <div class="card-body p-4">
                                <canvas id="radarChart-{{ $child->id }}" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- الرسم البياني الدائري للحضور --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold text-dark"><i class="fas fa-chart-pie text-success me-2"></i> نسبة الحضور والمواظبة</h5>
                            </div>
                            <div class="card-body p-4 text-center">
                                @if(array_sum($details->attendance) > 0)
                                    <canvas id="doughnutChart-{{ $child->id }}" style="max-height: 350px;"></canvas>
                                @else
                                    <div class="text-center text-muted h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-calendar-times fa-3x opacity-25 mb-3"></i>
                                        <p>لا يوجد سجل حضور وغياب مرصود حتى الآن.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- قائمة التقييمات التفصيلية (الأكورديون) مخفية في الطباعة --}}
                <div class="d-print-none">
                    <h5 class="fw-bold text-dark mb-3 mt-5"><i class="fas fa-list-ul text-warning me-2"></i> التقييمات التفصيلية للمواد:</h5>
                    <div class="accordion shadow-sm" id="accordion-{{ $child->id }}" style="border-radius: 1rem; overflow: hidden;">
                        @forelse($details->subjects as $subject)
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $child->id }}-{{ $loop->index }}">
                                        <div class="d-flex justify-content-between w-100 pe-3">
                                            <span><i class="fas fa-book text-dark me-2"></i> {{ $subject->subject_name }}</span>
                                            <span class="text-dark fw-bold">{{ $subject->total_score ?? '-' }} / {{ $subject->max_total ?? 100 }}</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse-{{ $child->id }}-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#accordion-{{ $child->id }}">
                                    <div class="accordion-body bg-light p-4">
                                        @if(isset($subject->detailed_marks) && count($subject->detailed_marks) > 0)
                                            <div class="table-responsive bg-white rounded shadow-sm border">
                                                <table class="table table-hover align-middle mb-0 text-center">
                                                    <thead class="table-light text-muted small">
                                                        <tr>
                                                            <th class="text-start ps-3">اسم التقييم / الاختبار</th>
                                                            <th>الدرجة العظمى</th>
                                                            <th>درجة الطالب</th>
                                                            <th>الفصل الدراسي</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subject->detailed_marks as $mark)
                                                        <tr>
                                                            <td class="fw-bold text-dark text-start ps-3">{{ $mark->title }}</td>
                                                            <td class="text-muted">{{ $mark->max_score }}</td>
                                                            <td>
                                                                @if($mark->score !== null)
                                                                    <span class="badge bg-success px-3 py-2 fs-6">{{ $mark->score }}</span>
                                                                @else
                                                                    <span class="badge bg-warning text-dark px-2 py-1">لم تُرصد</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-muted small">الفصل {{ $mark->semester ?? 1 }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-3 text-muted">
                                                <i class="fas fa-info-circle mb-2 fa-2x opacity-25"></i>
                                                <p class="mb-0">لم يقم المعلم بإضافة تقييمات يومية لهذه المادة حتى الآن.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 bg-white">
                                <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                                <p class="text-muted fw-bold">لم يتم تسجيل مواد لهذا الطالب بعد.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- البيانات المخصصة للجافاسكريبت (مخفية) --}}
                <div id="chartData-{{ $child->id }}" 
                     data-labels="{{ $details->chartLabels }}" 
                     data-data="{{ $details->chartData }}"
                     data-attendance='@json($details->attendance)'>
                </div>

            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5 bg-white shadow-sm border-0" style="border-radius: 1rem;">
        <i class="fas fa-child fa-4x text-muted opacity-25 mb-3"></i>
        <h4 class="text-muted fw-bold">لا يوجد أبناء مرتبطين بحسابك حالياً.</h4>
        <p class="small text-muted mb-0">يرجى التواصل مع إدارة المدرسة لتسجيل وربط أبنائك بحسابك.</p>
    </div>
@endif

@endsection

@section('styles')
<style>
    /* تأثيرات التبويبات */
    .nav-link.active .child-card { border: 2px solid #212529 !important; background-color: #f8f9fa !important; box-shadow: 0 .5rem 1rem rgba(33, 37, 41, 0.15) !important; transform: translateY(-3px); }
    .hover-scale { transition: all 0.2s ease-in-out; cursor: pointer; }
    .nav-link:not(.active) .hover-scale:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,0.1) !important; }
    .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: #212529; box-shadow: none; }
    .accordion-button:focus { box-shadow: none; border-color: rgba(33,37,41,.125); }

    /* 📜 تنسيقات الشهادة الرسمية */
    .certificate-wrapper { border-radius: 12px; padding: 20px; overflow-x: auto; }
    .certificate-container { 
        min-width: 800px;
        max-width: 210mm; 
        margin: auto; 
        padding: 40px; 
        border: 5px double #444; 
        position: relative;
        background: #fff;
        color: #000;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
    .student-info { background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .marks-table th { background-color: #eee !important; border: 1px solid #000 !important; vertical-align: middle; }
    .marks-table td { border: 1px solid #000 !important; vertical-align: middle; }
    .result-box { background-color: #f8f9fa; border: 1px solid #ddd; }
    .stamp-overlay { position: absolute; bottom: 80px; left: 80px; opacity: 0.08; transform: rotate(-15deg); pointer-events: none; }
    .stamp-circle { border: 5px solid #000; border-radius: 50%; width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; }

    /* 🖨️ تنسيقات الطباعة */
    @media print {
        body * { visibility: hidden !important; }
        .printing-active, .printing-active * { visibility: visible !important; }
        .printing-active { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            border: none !important; 
            padding: 0 !important;
            margin: 0 !important;
            display: block !important; /* ضمان ظهورها عند الطباعة حتى لو كانت مخفية */
        }
        .marks-table th { background-color: #ddd !important; -webkit-print-color-adjust: exact; }
        .student-info, .result-box { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
        @page { margin: 10mm; size: A4; }
    }
</style>

<script>
    // 💡 تغيير نص الزر عند الفتح والإغلاق
    function toggleCertText(btn) {
        let span = btn.querySelector('span');
        let icon = btn.querySelector('i');
        
        if (span.innerText === 'عرض الشهادة') {
            span.innerText = 'إخفاء الشهادة';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            span.innerText = 'عرض الشهادة';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // 💡 دالة الجافاسكريبت لطباعة شهادة الابن المحدد فقط
    function printChildCertificate(certId) {
        // إضافة الكلاس الذي يظهر الشهادة للطباعة
        document.body.classList.add('printing-mode');
        let certElement = document.getElementById(certId);
        certElement.classList.add('printing-active');
        
        window.print();
        
        // إزالة الكلاس بعد الطباعة لتعود الصفحة لشكلها الطبيعي
        document.body.classList.remove('printing-mode');
        certElement.classList.remove('printing-active');
    }
    // رسم الرسوم البيانية عند تحميل الصفحة
    document.addEventListener("DOMContentLoaded", function() {
        const tabContents = document.querySelectorAll('.tab-pane');
        
        tabContents.forEach(pane => {
            const childId = pane.id.split('-').pop();
            const dataDiv = document.getElementById('chartData-' + childId);
            
            if (dataDiv) {
                // Bar Chart - الأداء الأكاديمي
                const radarCanvas = document.getElementById('radarChart-' + childId);
                if (radarCanvas) {
                    const labelsText = dataDiv.getAttribute('data-labels');
                    const dataText = dataDiv.getAttribute('data-data');
                    let labels = [];
                    let data = [];
                    try { labels = JSON.parse(labelsText || '[]'); } catch(e){}
                    try { data = JSON.parse(dataText || '[]'); } catch(e){}
                    
                    if (labels.length > 0) {
                        let ctx = radarCanvas.getContext('2d');
                        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
                        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.3)');

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'مستوى الأداء %',
                                    data: data,
                                    backgroundColor: gradient,
                                    borderColor: '#36a2eb',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                    barPercentage: 0.6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                                        ticks: { stepSize: 20, font: { family: 'Tahoma', size: 12 }, callback: value => value + '%' }
                                    },
                                    x: {
                                        grid: { display: false, drawBorder: false },
                                        ticks: { font: { family: 'Tahoma', size: 12, weight: 'bold' } }
                                    }
                                },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        titleFont: { family: 'Tahoma', size: 14 },
                                        bodyFont: { family: 'Tahoma', size: 13, weight: 'bold' },
                                        callbacks: { label: context => 'المستوى: ' + context.parsed.y + '%' }
                                    }
                                },
                                animation: { duration: 1500, easing: 'easeOutQuart' }
                            }
                        });
                    } else {
                        const ctx = radarCanvas.getContext('2d');
                        ctx.font = "14px Tahoma";
                        ctx.fillStyle = "#999";
                        ctx.textAlign = "center";
                        ctx.fillText("لا توجد تقييمات مرصودة حتى الآن", radarCanvas.width/2, radarCanvas.height/2);
                    }
                }
                
                // Doughnut Chart - الحضور
                const doughnutCanvas = document.getElementById('doughnutChart-' + childId);
                if (doughnutCanvas) {
                    const attendanceText = dataDiv.getAttribute('data-attendance');
                    let attendance = {};
                    try { attendance = JSON.parse(attendanceText || '{}'); } catch(e){}
                    
                    const present = attendance.present || 0;
                    const absent = attendance.absent || 0;
                    const late = attendance.late || 0;
                    
                    if (present + absent + late > 0) {
                        new Chart(doughnutCanvas.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['حضور', 'تأخير', 'غياب'],
                                datasets: [{
                                    data: [present, late, absent],
                                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '75%',
                                plugins: {
                                    legend: { position: 'bottom', labels: { font: { family: 'Tahoma' } } }
                                }
                            }
                        });
                    }
                }
            }
        });
    });
</script>
@endsection