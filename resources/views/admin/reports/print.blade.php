<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الأوائل - {{ $selectedGrade->name }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-print-color-adjust: exact; /* لضمان طباعة الألوان والخلفيات */
        }
        
        .report-container {
            max-width: 210mm; /* عرض ورقة A4 */
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd; /* حدود وهمية للشاشة فقط */
        }

        .header-section {
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid #000 !important; /* حدود سوداء واضحة */
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f2f2f2 !important;
            color: #000;
        }

        .footer-section {
            margin-top: 50px;
        }

        /* إعدادات الطابعة */
        @media print {
            .report-container {
                border: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .no-print {
                display: none;
            }
            @page {
                size: A4;
                margin: 2cm;
            }
        }
    </style>
</head>
<body onload="window.print()"> <div class="text-center mt-3 no-print">
        <button onclick="window.close()" class="btn btn-secondary px-4">إغلاق الصفحة</button>
        <button onclick="window.print()" class="btn btn-primary px-4">طباعة مرة أخرى</button>
    </div>

    <div class="report-container">
        
        <div class="header-section text-center">
            <div class="row align-items-center">
                
                <div class="col-4 text-end">
                    <h4 class="fw-bold mb-0">{{ $school->name ?? 'اسم المدرسة' }}</h4>
                </div>

                <div class="col-4">
                    {{-- إذا كان للمدرسة شعار يمكن إضافته هنا --}}
                    {{-- <img src="{{ asset('storage/' . $school->logo) }}" width="80"> --}}
                    <h3 class="fw-bold mt-2">كشف العشرة الأوائل</h3>
                </div>

                <div class="col-4 text-start">
                    <p class="mb-1"><strong>العام الدراسي:</strong> {{ date('Y') }}</p>
                    <p class="mb-0"><strong>تاريخ التقرير:</strong> {{ date('Y-m-d') }}</p>
                </div>
            </div>
            
            <div class="mt-3">
                <span class="badge bg-white text-dark border border-dark px-3 py-2 fs-6">
                    الصف: {{ $selectedGrade->name }}
                </span>
            </div>
        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th style="width: 10%">الترتيب</th>
                    <th style="width: 35%">اسم الطالب</th>
                    <th style="width: 20%">الشعبة</th>
                    <th style="width: 15%">المجموع</th>
                    <th style="width: 20%">النسبة المئوية</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topStudents as $index => $student)
                <tr>
                    <td class="fw-bold">{{ $index + 1 }}</td>
                    <td class="text-end px-3">{{ $student->name }}</td>
                    <td>{{ $student->studentProfile->schoolClass->section ?? '-' }}</td>
                    <td class="fw-bold">{{ number_format($student->total_final_score, 1) }}</td>
                    <td>{{ number_format($student->percentage, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer-section row text-center">
            <div class="col-6">
                <p class="fw-bold mb-5">شؤون الطلبة والامتحانات</p>
                <p>.............................</p>
            </div>
            <div class="col-6">
                <p class="fw-bold mb-5">مدير المدرسة</p>
                <p>.............................</p>
            </div>
        </div>

    </div>

</body>
</html>