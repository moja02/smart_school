@extends('layouts.parent')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h2 class="fw-bold text-primary"><i class="fas fa-certificate me-2"></i> الشهادة التفصيلية</h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4"><i class="fas fa-print me-2"></i> طباعة الشهادة</button>
            <a href="{{ route('parent.children') }}" class="btn btn-secondary rounded-pill px-4 ms-2">الرجوع</a>
        </div>
    </div>

    <!-- Certificate Card -->
    <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-primary text-white p-4 text-center" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
            <div class="d-flex justify-content-between align-items-center px-3">
                <div class="text-end">
                    <h5 class="fw-bold mb-1">{{ $school->name }}</h5>
                    <small>العام الدراسي الحالي</small>
                </div>
                <div class="display-4"><i class="fas fa-graduation-cap"></i></div>
                <div class="text-start">
                    <h5 class="fw-bold mb-1">الشهادة الأكاديمية</h5>
                    <small>تقرير أداء الطالب</small>
                </div>
            </div>
        </div>
        
        <div class="card-body p-5">
            <div class="row mb-5 bg-light p-3 rounded-3">
                <div class="col-md-4">
                    <p class="mb-1 text-muted">اسم الطالب</p>
                    <h4 class="fw-bold text-dark">{{ $student->name }}</h4>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">الصف الدراسي</p>
                    <h4 class="fw-bold text-dark">{{ $student->studentProfile->schoolClass->grade->name ?? '' }}</h4>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">الفصل / الشعبة</p>
                    <h4 class="fw-bold text-dark">{{ $student->studentProfile->schoolClass->name ?? '' }}</h4>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th rowspan="2" class="align-middle">المادة</th>
                            <th colspan="2">الفصل الأول</th>
                            <th colspan="2">الفصل الثاني</th>
                            <th rowspan="2" class="align-middle">المجموع النهائي (100)</th>
                            <th rowspan="2" class="align-middle">التقدير</th>
                        </tr>
                        <tr>
                            <th>أعمال</th>
                            <th>نهائي</th>
                            <th>أعمال</th>
                            <th>نهائي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($marks as $mark)
                        <tr>
                            <td class="fw-bold text-end pe-4">{{ $mark['subject_name'] }}</td>
                            <td>{{ $mark['works_sem1'] }}</td>
                            <td>{{ $mark['final_sem1'] }}</td>
                            <td>{{ $mark['works_sem2'] }}</td>
                            <td>{{ $mark['final_sem2'] }}</td>
                            <td class="fw-bold">{{ $mark['total'] }}</td>
                            <td>
                                <span class="badge {{ $mark['total'] >= 85 ? 'bg-success' : ($mark['total'] >= 65 ? 'bg-primary' : 'bg-warning') }}">
                                    {{ $mark['grade_letter'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end pe-4">المجموع الكلي</td>
                            <td class="fs-5 text-primary">{{ $totalSum }} / {{ $maxPossibleSum }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5 text-center g-4">
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-light">
                        <p class="text-muted mb-1">النسبة المئوية</p>
                        <h2 class="fw-bold text-primary mb-0">{{ $percentage }}%</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-light">
                        <p class="text-muted mb-1">التقدير العام</p>
                        <h2 class="fw-bold {{ $percentage >= 85 ? 'text-success' : 'text-primary' }} mb-0">{{ $overallGrade }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-light">
                        <p class="text-muted mb-1">النتيجة</p>
                        <h2 class="fw-bold {{ $percentage >= 50 ? 'text-success' : 'text-danger' }} mb-0">
                            {{ $percentage >= 50 ? 'ناجح' : 'راسب' }}
                        </h2>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 text-center text-muted d-none d-print-block">
                <p>تم استخراج هذه الشهادة من نظام Smart School إلكترونياً.</p>
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between px-5">
                        <p>توقيع مدير المدرسة</p>
                        <p>ختم المدرسة</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    @media print {
        body { background-color: #fff; }
        .sidebar, .navbar, .page-header-card { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
