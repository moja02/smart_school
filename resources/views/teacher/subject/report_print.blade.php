<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير درجات - {{ $class->name }}</title>
    
    {{-- استخدام Bootstrap لتنسيق سريع وجميل --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- خط عربي جميل من Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Cairo', sans-serif; background: #fff; }
        
        .report-header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .table thead th { background-color: #f8f9fa !important; border-bottom: 2px solid #000; print-color-adjust: exact; }
        
        /* إخفاء الأزرار عند الطباعة */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            /* ضمان ظهور ألوان الخلفية في الطباعة */
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="container py-4">
        {{-- أزرار التحكم (تختفي عند الطباعة) --}}
        <div class="d-flex justify-content-between mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary px-4">
                <i class="fas fa-print"></i> طباعة / حفظ PDF
            </button>
            <button onclick="window.history.back()" class="btn btn-secondary px-4">رجوع</button>
        </div>

        {{-- رأس التقرير --}}
        <div class="report-header text-center">
            <h2 class="fw-bold">تقرير درجات الطلاب</h2>
            <h4 class="mt-2">{{ $subject->name }} - {{ $class->name }}</h4>
            <p class="text-muted">تاريخ التقرير: {{ date('Y/m/d') }}</p>
        </div>

        {{-- جدول الدرجات --}}
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th class="text-start" style="width: 25%">اسم الطالب</th>
                        @foreach($assessments as $assessment)
                            <th>{{ $assessment->title }} <br> <small>({{ $assessment->max_score }})</small></th>
                        @endforeach
                        <th>اختبارات ذاتية</th>
                        <th class="bg-light">المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($class->students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-start fw-bold">{{ $student->user->name }}</td>

                        @php $totalScore = 0; @endphp
                        @foreach($assessments as $assessment)
                            @php
                                $mark = $marks->where('student_id', $student->id)->where('assessment_id', $assessment->id)->first();
                                if($mark) $totalScore += $mark->score;
                            @endphp
                            <td>{{ $mark ? $mark->score : '-' }}</td>
                        @endforeach

                        @php
                            $quizCount = $quizAttempts->where('student_id', $student->id)->count();
                        @endphp
                        <td>{{ $quizCount ?: '-' }}</td>

                        <td class="fw-bold bg-light">{{ $totalScore }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- كود JS لفتح نافذة الطباعة تلقائياً --}}
    <script>
        window.onload = function() {
            // يمكنك تفعيل هذا السطر إذا أردت فتح نافذة الطباعة فوراً عند تحميل الصفحة
            // window.print();
        };
    </script>

</body>
</html>