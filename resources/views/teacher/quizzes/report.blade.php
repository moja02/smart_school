<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير الاختبار - {{ $quiz->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; padding: 0 !important; }
            .container { max-width: 100% !important; width: 100% !important; }
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .paper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .header-table { width: 100%; border-bottom: 2px solid #000; mb-4: ; padding-bottom: 10px; }
        .question-item { margin-bottom: 25px; page-break-inside: avoid; }
        .circle { width: 15px; height: 15px; border: 1px solid #000; border-radius: 50%; display: inline-block; margin-left: 10px; }
    </style>
</head>
<body>

    <div class="container no-print mt-4 mb-4 text-center">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5 shadow">
            <i class="fas fa-print"></i> بدء الطباعة (حفظ كـ PDF)
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg px-4 shadow">إغلاق الصفحة</button>
    </div>

    <div class="paper">
        <table class="header-table">
            <tr>
                <td style="width: 33%">
                    <h6 class="fw-bold">المادة: {{ $quiz->subject_name }}</h6>
                    <h6 class="fw-bold">الشعبة: {{ $quiz->section_name }}</h6>
                </td>
                <td style="width: 34%; text-align: center;">
                    <h3 class="fw-bold text-decoration-underline">{{ $quiz->title }}</h3>
                    <p class="mb-0">العام الدراسي: 2025 - 2026</p>
                </td>
                <td style="width: 33%; text-align: left;">
                    <h6 class="fw-bold">الزمن: {{ $quiz->duration }} دقيقة</h6>
                    <h6 class="fw-bold">التاريخ: {{ date('Y/m/d') }}</h6>
                </td>
            </tr>
        </table>

        <div class="row mt-4 mb-4 pb-3 border-bottom">
            <div class="col-8">
                <h5 class="fw-bold">اسم الطالب: .................................................................</h5>
            </div>
            
        </div>

        @if($quiz->description)
        <p class="text-center fst-italic mb-4 border p-2 bg-light">{{ $quiz->description }}</p>
        @endif

        <div class="questions mt-4">
            @foreach($questions as $index => $q)
            <div class="question-item">
                <h5 class="fw-bold">{{ $index + 1 }}. {{ $q->content }}</h5>
                
                @if($q->type == 'multiple_choice' && !empty($q->options))
                    @php $options = json_decode($q->options); @endphp
                    <div class="row mt-2">
                        @foreach($options as $opt)
                        <div class="col-6 mb-2">
                            <span class="circle"></span> {{ $opt }}
                        </div>
                        @endforeach
                    </div>
                @elseif($q->type == 'true_false')
                    <div class="mt-2 d-flex gap-5">
                        <div><span class="circle"></span> صح</div>
                        <div><span class="circle"></span> خطأ</div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <div style="position: absolute; bottom: 20mm; width: 100%; text-align: center;" class="mt-5">
            <hr>
            <p class="fw-bold">انتهت الأسئلة - مع تمنياتنا لكم بالتوفيق والنجاح</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>