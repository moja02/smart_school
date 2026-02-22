<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير نتائج: {{ $quiz->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; background: white !important; }
        .header-print { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        @media print {
            .no-print { display: none; }
            @page { margin: 2cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container mt-5">
        <div class="header-print d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">تقرير نتائج الاختبار</h2>
                <p class="mb-0 text-muted">اسم الاختبار: {{ $quiz->title }}</p>
                <p class="mb-0 text-muted">التاريخ: {{ date('Y-m-d') }}</p>
            </div>
            <div class="text-start">
                <h5 class="fw-bold">نظام Smart School</h5>
                <p class="small text-muted">سجل الدرجات الرسمي</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-4 border p-2 text-center">المتقدمين: <strong>{{ $results->count() }}</strong></div>
            <div class="col-4 border p-2 text-center">المتوسط: <strong>{{ round($results->avg('score'), 1) }}</strong></div>
            <div class="col-4 border p-2 text-center">الدرجة النهائية: <strong>{{ $results->first()->total ?? '-' }}</strong></div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>اسم الطالب</th>
                    <th class="text-center">الدرجة</th>
                    <th class="text-center">النسبة</th>
                    <th class="text-center">الوقت المستغرق</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $index => $result)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $result->student_name }}</td>
                    <td class="text-center">{{ $result->score }} / {{ $result->total }}</td>
                    <td class="text-center">{{ round(($result->score / $result->total) * 100) }}%</td>
                    <td class="text-center">{{ $result->time_spent }} دقيقة</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-5 d-flex justify-content-between">
            <p>توقيع المعلم: ..........................</p>
            <p>ختم المدرسة: ..........................</p>
        </div>

        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary">إعادة الطباعة</button>
            <button onclick="window.close()" class="btn btn-secondary">إغلاق الصفحة</button>
        </div>
    </div>

</body>
</html>