<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شهادة الطالب: {{ $student->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-family: 'Segoe UI', Tahoma, sans-serif; -webkit-print-color-adjust: exact; }
        .certificate-container { 
            max-width: 210mm; margin: auto; padding: 20px; 
            border: 5px double #444; /* إطار مزدوج للشهادة */
            height: 290mm; /* ارتفاع A4 تقريباً */
            position: relative;
        }
        .header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .student-info { background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        
        /* تنسيقات الجدول */
        .marks-table th { background-color: #eee !important; border: 1px solid #000 !important; vertical-align: middle; }
        .marks-table td { border: 1px solid #000 !important; vertical-align: middle; }
        
        .footer { margin-top: 50px; }
        @media print {
            .no-print { display: none !important; }
            .certificate-container { border: none; height: auto; }
            @page { margin: 10mm; size: A4; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center mt-3 no-print">
        <button onclick="window.close()" class="btn btn-secondary">إغلاق</button>
        <button onclick="window.print()" class="btn btn-primary">طباعة</button>
    </div>

    <div class="certificate-container">
        <div class="header text-center row align-items-center">
            <div class="col-4 text-end">
                <h5 class="mb-1">{{ $school->name ?? 'مدرسة الذكاء الحديثة' }}</h5>
                <p class="mb-0 text-muted">الإدارة المدرسية</p>
            </div>
            <div class="col-4">
                <h2 class="fw-bold text-decoration-underline">كشف درجات طالب</h2>
                <p class="mb-0">العام الدراسي {{ date('Y') }}</p>
            </div>
            <div class="col-4 text-start">
                <p class="mb-0">التاريخ: {{ date('Y-m-d') }}</p>
            </div>
        </div>

        <div class="student-info row mx-0">
            <div class="col-6 mb-2"><strong>اسم الطالب:</strong> {{ $student->name }}</div>
            <div class="col-6 mb-2"><strong>رقم القيد:</strong> {{ $student->id }}</div>
            <div class="col-6"><strong>الصف الدراسي:</strong> {{ $student->studentProfile->schoolClass->grade->name ?? '-' }}</div>
            <div class="col-6"><strong>الشعبة / الفصل:</strong> {{ $student->studentProfile->schoolClass->section ?? '-' }}</div>
        </div>

        <table class="table table-bordered text-center marks-table">
            <thead>
                {{-- الصف الأول من الترويسة --}}
                <tr>
                    <th rowspan="2" style="width: 26%">المادة الدراسية</th>
                    <th colspan="2">الفصل الدراسي الأول</th>
                    <th colspan="2">الفصل الدراسي الثاني</th>
                    <th rowspan="2" style="width: 12%">المجموع الكلي</th>
                    <th rowspan="2" style="width: 14%">التقدير</th>
                </tr>
                {{-- الصف الثاني من الترويسة (التقسيمات الفرعية) --}}
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
                    <td class="text-end px-3 fw-bold">{{ $mark->subject_name }}</td>
                    
                    {{-- درجات الفصل الأول --}}
                    <td>{{ $mark->works_score_sem1 ?? '-' }}</td>
                    <td>{{ $mark->final_score_sem1 ?? '-' }}</td>
                    
                    {{-- درجات الفصل الثاني --}}
                    <td>{{ $mark->works_score_sem2 ?? '-' }}</td>
                    <td>{{ $mark->final_score_sem2 ?? '-' }}</td>
                    
                    {{-- المجموع والتقدير --}}
                    <td class="fw-bold bg-light fs-6">{{ $mark->total_score ?? 0 }}</td>
                    <td class="fw-bold">
                        @if(($mark->total_score ?? 0) >= 85) <span class="text-success">ممتاز</span>
                        @elseif(($mark->total_score ?? 0) >= 75) جيد جداً
                        @elseif(($mark->total_score ?? 0) >= 65) جيد
                        @elseif(($mark->total_score ?? 0) >= 50) مقبول
                        @else <span class="text-danger">ضعيف</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-5 text-muted">لم يتم رصد درجات لهذا الطالب بعد.</td>
                </tr>
                @endforelse
            </tbody>
            
            {{-- صف المجموع النهائي --}}
            @if(count($marks) > 0)
            <tfoot class="fw-bold" style="border-top: 3px double #000;">
                <tr>
                    {{-- تم تغيير colspan إلى 5 ليتناسب مع عدد الأعمدة الجديد --}}
                    <td colspan="5" class="text-end px-3 fs-5">المجموع الكلي للدرجات</td>
                    <td class="bg-light fs-5">{{ $totalSum }}</td>
                    <td class="fs-5">{{ number_format($percentage, 1) }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="mt-4 p-3 border rounded text-center">
            <strong>النتيجة العامة: </strong>
            @if(count($marks) > 0)
                @if($percentage >= 50)
                    <span class="text-success fw-bold fs-4">ناجح ومنقول للصف التالي</span>
                @else
                    <span class="text-danger fw-bold fs-4">راسب (له دور ثاني)</span>
                @endif
            @else
                <span>---</span>
            @endif
        </div>

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

        <div style="position: absolute; bottom: 80px; left: 80px; opacity: 0.1; transform: rotate(-15deg);">
            <div style="border: 5px solid #000; border-radius: 50%; width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
                <h4 class="m-0">ختم المدرسة</h4>
            </div>
        </div>
    </div>
</body>
</html>