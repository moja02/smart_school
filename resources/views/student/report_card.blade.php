@extends('layouts.student')

@section('content')

{{-- ترويسة الصفحة وزر الطباعة (يختفي عند الطباعة) --}}
<div class="card page-header-card mb-4 shadow border-0 d-print-none">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">الشهادة المدرسية 📄</h2>
            <p class="text-white-50 mb-0">عرض الكشف الرسمي المعتمد لدرجاتك.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-light shadow-sm fw-bold px-4 text-primary">
                <i class="fas fa-print me-2"></i> طباعة الشهادة
            </button>
        </div>
    </div>
</div>

{{-- 📜 بداية تصميم الشهادة الرسمي --}}
<div class="certificate-wrapper bg-white shadow rounded mb-5 p-2 p-md-4">
    <div class="certificate-container" id="printable-area">
        
        {{-- ترويسة الشهادة (لوغو وإدارة) --}}
        <div class="header text-center row align-items-center">
            <div class="col-4 text-end">
                <h5 class="mb-1 fw-bold">{{ $school->name ?? 'مدرسة الذكاء الحديثة' }}</h5>
                <p class="mb-0 text-muted">الإدارة المدرسية</p>
            </div>
            <div class="col-4">
                <h2 class="fw-bold text-decoration-underline text-primary">كشف درجات طالب</h2>
                <p class="mb-0">العام الدراسي {{ date('Y') }}</p>
            </div>
            <div class="col-4 text-start">
                <p class="mb-0">التاريخ: {{ date('Y-m-d') }}</p>
            </div>
        </div>

        {{-- بيانات الطالب --}}
        <div class="student-info row mx-0">
            <div class="col-6 mb-2"><strong>اسم الطالب:</strong> <span class="text-primary fw-bold">{{ $user->name }}</span></div>
            <div class="col-6 mb-2"><strong>رقم القيد:</strong> {{ $user->id }}</div>
            <div class="col-6"><strong>الصف الدراسي:</strong> {{ $class->grade->name ?? '-' }}</div>
            <div class="col-6"><strong>الشعبة / الفصل:</strong> {{ $class->section ?? '-' }}</div>
        </div>

        {{-- جدول الدرجات المقسّم --}}
        <table class="table table-bordered text-center marks-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 26%">المادة الدراسية</th>
                    <th colspan="2">الفصل الدراسي الأول</th>
                    <th colspan="2">الفصل الدراسي الثاني</th>
                    <th rowspan="2" style="width: 12%">المجموع الكلي</th>
                    <th rowspan="2" style="width: 14%">التقدير</th>
                </tr>
                <tr>
                    <th style="width: 12%">أعمال</th>
                    <th style="width: 12%">امتحان</th>
                    <th style="width: 12%">أعمال</th>
                    <th style="width: 12%">امتحان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($marks as $mark)
                <tr>
                    <td class="text-end px-3 fw-bold text-dark">{{ $mark->subject_name }}</td>
                    
                    <td>{{ $mark->works_score_sem1 }}</td>
                    <td>{{ $mark->final_score_sem1 }}</td>
                    
                    <td>{{ $mark->works_score_sem2 }}</td>
                    <td>{{ $mark->final_score_sem2 }}</td>
                    
                    <td class="fw-bold bg-light fs-6 text-primary">{{ $mark->total_score }}</td>
                    <td class="fw-bold">
                        @if(($mark->total_score !== '-' ? $mark->total_score : 0) >= 85) <span class="text-success">ممتاز</span>
                        @elseif(($mark->total_score !== '-' ? $mark->total_score : 0) >= 75) جيد جداً
                        @elseif(($mark->total_score !== '-' ? $mark->total_score : 0) >= 65) جيد
                        @elseif(($mark->total_score !== '-' ? $mark->total_score : 0) >= 50) مقبول
                        @elseif($mark->total_score !== '-') <span class="text-danger">ضعيف</span>
                        @else - 
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-5 text-muted">لم يتم رصد درجات لك بعد.</td>
                </tr>
                @endforelse
            </tbody>
            
            {{-- صف المجموع النهائي --}}
            @if(count($marks) > 0)
            <tfoot class="fw-bold" style="border-top: 3px double #000;">
                <tr>
                    <td colspan="5" class="text-end px-3 fs-5">المجموع الكلي للدرجات</td>
                    <td class="bg-light fs-5 text-primary">{{ $totalSum }}</td>
                    <td class="fs-5">{{ number_format($percentage, 1) }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- النتيجة العامة --}}
        <div class="mt-4 p-3 border rounded text-center result-box">
            <strong>النتيجة العامة: </strong>
            @if(count($marks) > 0)
                @if($percentage >= 50)
                    <span class="text-success fw-bold fs-4 ms-2">ناجح ومنقول للصف التالي</span>
                @else
                    <span class="text-danger fw-bold fs-4 ms-2">راسب (له دور ثاني)</span>
                @endif
            @else
                <span>---</span>
            @endif
        </div>

        {{-- التوقيعات --}}
        <div class="footer row text-center">
            <div class="col-4">
                <p class="fw-bold mb-5">مربّي الفصل</p>
                <p>..................</p>
            </div>
            <div class="col-4">
                <p class="fw-bold mb-5">شؤون الامتحانات</p>
                <p>..................</p>
            </div>
            <div class="col-4">
                <p class="fw-bold mb-5">مدير المدرسة</p>
                <p>..................</p>
            </div>
        </div>

        {{-- الختم الوهمي --}}
        <div class="stamp-overlay">
            <div class="stamp-circle">
                <h4 class="m-0">ختم المدرسة</h4>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* تنسيقات العرض على الشاشة */
    .certificate-wrapper { overflow-x: auto; }
    .certificate-container { 
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
    
    .result-box { background-color: #f8f9fa; }
    .footer { margin-top: 50px; }
    
    .stamp-overlay { position: absolute; bottom: 80px; left: 80px; opacity: 0.08; transform: rotate(-15deg); pointer-events: none; }
    .stamp-circle { border: 5px solid #000; border-radius: 50%; width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; }

    /* 🖨️ تنسيقات الطباعة الصارمة */
    @media print {
        /* إخفاء كل أشرطة التصفح والقوائم الجانبية الموروثة من layouts.student */
        body * { visibility: hidden !important; }
        
        /* إظهار منطقة الشهادة فقط */
        #printable-area, #printable-area * { visibility: visible !important; }
        
        /* توسيع الشهادة لتأخذ حجم ورقة A4 بالكامل */
        #printable-area { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            border: none !important; 
            padding: 0 !important;
        }
        
        /* تحسين الألوان في الطباعة */
        .marks-table th { background-color: #ddd !important; -webkit-print-color-adjust: exact; }
        .student-info, .result-box { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
        
        @page { margin: 10mm; size: A4; }
    }
</style>
@endsection