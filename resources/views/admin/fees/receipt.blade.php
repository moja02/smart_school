<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال سداد - {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; background: #f9f9f9; }
        .receipt-container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0 0; color: #666; }
        .details table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details table th, .details table td { padding: 10px; border: 1px solid #ddd; text-align: right; }
        .details table th { background: #f0f0f0; width: 40%; }
        .footer { margin-top: 40px; text-align: center; font-size: 0.9em; color: #777; border-top: 1px solid #eee; padding-top: 20px; }
        .print-btn { display: block; margin: 20px auto; padding: 10px 20px; background: #007bff; color: white; text-align: center; border: none; cursor: pointer; font-size: 16px; border-radius: 5px; text-decoration: none; max-width: 150px; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-container { box-shadow: none; border: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        <h1>إيصال سداد رسوم دراسية</h1>
        <p>مدرسة التفوق الذكية</p>
    </div>

    <div class="details">
        <table>
            <tr>
                <th>رقم الإيصال</th>
                <td style="font-weight: bold; color: #d9534f;">{{ $payment->receipt_number }}</td>
            </tr>
            <tr>
                <th>تاريخ السداد</th>
                <td>{{ $payment->payment_date }}</td>
            </tr>
            <tr>
                <th>اسم الطالب</th>
                <td>{{ $payment->student->user->name }}</td>
            </tr>
            <tr>
                <th>المبلغ المدفوع</th>
                <td style="font-weight: bold; font-size: 1.2em;">{{ number_format($payment->amount, 2) }} دينار</td>
            </tr>
            <tr>
                <th>المتبقي على الطالب</th>
                <td>{{ number_format($payment->student->remaining_fees, 2) }} دينار</td>
            </tr>
            @if($payment->notes)
            <tr>
                <th>ملاحظات</th>
                <td>{{ $payment->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>هذا الإيصال مُصدر إلكترونياً من النظام ولا يحتاج إلى توقيع.</p>
        <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</div>

<button onclick="window.print()" class="print-btn">طباعة الإيصال</button>

</body>
</html>
