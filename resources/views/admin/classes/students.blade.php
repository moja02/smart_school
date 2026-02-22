@extends('layouts.admin')

@section('content')

{{-- تنسيقات خاصة للطباعة --}}
<style>
    @media print {
        /* إخفاء العناصر غير الضرورية */
        .sidebar, .navbar, .page-header-card, .btn, .no-print {
            display: none !important;
        }
        
        /* توسيع المحتوى ليشمل كامل الصفحة */
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        body {
            background-color: white !important;
            font-size: 12pt; /* حجم خط مناسب للورق */
        }

        /* إظهار ترويسة الطباعة المخفية */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        /* تحسين الجدول للطباعة */
        table {
            width: 100% !important;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            color: #000 !important;
        }
        
        /* إجبار المتصفح على طباعة الألوان الخلفية (للجدول) */
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact;
    }

    /* إخفاء ترويسة الطباعة في العرض العادي */
    .print-header {
        display: none;
    }
</style>

{{-- ترويسة الطباعة (تظهر فقط في الورقة) --}}
<div class="print-header">
    <h2 class="fw-bold">Smart School</h2>
    <h4>تقرير قائمة الفصل الدراسي</h4>
    <p>العام الدراسي: {{ date('Y') }} - {{ date('Y')+1 }}</p>
</div>

{{-- واجهة العرض العادية --}}
<div class="card page-header-card mb-4 shadow no-print">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-users-class me-2"></i> {{ $class->grade->name }} - {{ $class->name }} ({{ $class->section }})
            </h3>
            <p class="mb-0 opacity-75">قائمة الطلاب المسجلين في هذا الفصل (العدد: {{ $students->count() }})</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-print me-1"></i> طباعة القائمة
            </button>
            <a href="{{ route('admin.classes') }}" class="btn btn-outline-light ms-2">عودة</a>
        </div>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body p-4">
        
        {{-- معلومات الفصل (تظهر في الصفحة والطباعة) --}}
        <div class="row mb-4 bg-light p-3 rounded border mx-0">
            <div class="col-md-4"><strong>المرحلة:</strong> {{ $class->grade->name }}</div>
            <div class="col-md-4"><strong>الصف:</strong> {{ $class->name }}</div>
            <div class="col-md-4"><strong>الشعبة:</strong> <span class="badge bg-primary fs-6">{{ $class->section }}</span></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">اسم الطالب</th>
                        <th>البريد الإلكتروني</th>
                        
                        <th class="no-print">الملف الشخصي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-start fw-bold" >{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        
                        <td class="no-print">
                            <a href="#" class="btn btn-sm btn-outline-info rounded-circle" title="عرض الملف">
                                <i class="fas fa-id-card"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                            <p>لا يوجد طلاب مسجلين في هذا الفصل حالياً.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 no-print text-muted small">
            <i class="fas fa-info-circle"></i> يمكنك طباعة هذا الكشف لاستخدامه في رصد الغياب أو الدرجات اليدوية.
        </div>

        {{-- تذييل الطباعة --}}
        <div class="print-header mt-5 pt-3 border-top" style="border-bottom: none; display: none;">
            <div class="d-flex justify-content-between px-5">
                <span>توقيع المعلم: ....................</span>
                <span>توقيع المدير: ....................</span>
            </div>
        </div>

    </div>
</div>

@endsection