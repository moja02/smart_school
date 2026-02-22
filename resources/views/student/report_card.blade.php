@extends('layouts.student')

@section('content')

{{-- ترويسة الصفحة مع زر الطباعة --}}
<div class="card page-header-card mb-4 shadow border-0 d-print-none">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">كشف الدرجات 📄</h2>
            <p class="text-white-50 mb-0">متابعة تحصيلك العلمي ونتائجك في المقررات الدراسية.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary shadow-sm fw-bold px-4">
                <i class="fas fa-print me-2"></i> طباعة الكشف
            </button>
        </div>
    </div>
</div>

{{-- الكشف الرسمي (هذا الجزء الذي سيتم طباعته) --}}
<div class="card shadow border-0 mb-4" id="printable-area">
    <div class="card-header bg-white text-center py-4 border-bottom-0">
        <h4 class="fw-bold text-primary mb-1">شهادة تقييم مستوى الطالب</h4>
        <h6 class="text-muted">العام الدراسي الحالي</h6>
    </div>
    
    <div class="card-body px-4 px-md-5 pb-5">
        
        {{-- بيانات الطالب --}}
        <div class="row mb-4 bg-light p-3 rounded-3 border">
            <div class="col-md-6 mb-2 mb-md-0">
                <span class="text-muted small d-block">اسم الطالب:</span>
                <span class="fw-bold text-dark fs-5"><i class="fas fa-user-graduate text-primary me-1"></i> {{ $user->name }}</span>
            </div>
            <div class="col-md-6">
                <span class="text-muted small d-block">الفصل الدراسي:</span>
                <span class="fw-bold text-dark fs-5"><i class="fas fa-chalkboard text-info me-1"></i> {{ $class->name }}</span>
            </div>
        </div>

        {{-- جدول الدرجات --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="py-3" style="width: 30%;">المادة الدراسية</th>
                        <th class="py-3">أعمال السنة<br><small>(من {{ $reportData[0]['max_works'] ?? 40 }})</small></th>
                        <th class="py-3">الامتحان النهائي<br><small>(من {{ $reportData[0]['max_final'] ?? 60 }})</small></th>
                        <th class="py-3">المجموع<br><small>(من {{ $reportData[0]['max_total'] ?? 100 }})</small></th>
                        <th class="py-3">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                        <tr>
                            <td class="text-start ps-3 fw-bold text-dark">{{ $row['name'] }}</td>
                            
                            {{-- يمكنك لاحقاً فصل الدرجات لو كانت مخزنة منفصلة، حالياً نجمعها في المجموع --}}
                            <td class="text-muted">-</td> 
                            <td class="text-muted">-</td> 
                            
                            <td class="fw-bold fs-5 text-{{ $row['status_color'] }}">
                                {{ $row['student_total'] }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $row['status_color'] }} px-3 py-2 rounded-pill">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-muted">لا توجد مواد أو درجات مسجلة حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end pe-4 fs-5">المجموع العام:</td>
                        <td class="fs-5 text-primary">{{ $totalStudentScore }} <small class="text-muted">من {{ $totalMaxScore }}</small></td>
                        <td>
                            <span class="badge bg-dark px-3 py-2 fs-6">{{ $percentage }}%</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- توقيع الإدارة --}}
        <div class="row text-center mt-5 pt-4 border-top d-none d-print-flex">
            <div class="col-6">
                <h6 class="fw-bold text-muted">توقيع المعلم / رائد الفصل</h6>
                <p>...................................</p>
            </div>
            <div class="col-6">
                <h6 class="fw-bold text-muted">توقيع مدير المدرسة</h6>
                <p>...................................</p>
            </div>
        </div>

    </div>
</div>

{{-- ستايل مخصص للطباعة --}}
<style>
    @media print {
        body * { visibility: hidden; }
        #printable-area, #printable-area * { visibility: visible; }
        #printable-area { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
        .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
        .bg-primary { background-color: #f8f9fa !important; color: #000 !important; }
    }
</style>

@endsection