@extends('layouts.teacher')

@section('content')
<div class="container-fluid py-4">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center bg-white rounded">
            <div>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="fas fa-poll-h me-2"></i> نتائج اختبار: {{ $quiz->title }}
                </h4>
                <p class="text-muted small mb-0 mt-1">عرض وتحليل درجات الطلاب والوقت المستغرق</p>
            </div>

            <div class="btn-group">
                <a href="{{ route('teacher.quizzes.show', $quiz->id) }}" class="btn btn-sm btn-outline-primary" title="عرض وطباعة الاختبار">
                    <i class="fas fa-eye"></i>
                </a>
                
                {{-- رابط صفحة الطباعة المخصصة التي أنشأناها --}}
                <a href="{{ route('teacher.quizzes.print', $quiz->id) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="طباعة كشف النتائج">
                    <i class="fas fa-print"></i>
                </a>

                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $quiz->id }})" title="حذف">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small">إجمالي المتقدمين</h6>
                            <h2 class="mb-0 fw-bold">{{ $results->count() }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small">متوسط الدرجات</h6>
                            <h2 class="mb-0 fw-bold">{{ $results->count() > 0 ? round($results->avg('score'), 1) : 0 }}</h2>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small">أسرع وقت حل</h6>
                            <h2 class="mb-0 fw-bold">{{ $results->min('time_spent') ?? 0 }} <small>د</small></h2>
                        </div>
                        <i class="fas fa-stopwatch fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-dark fw-bold">
                        <tr>
                            <th class="ps-4 py-3">اسم الطالب</th>
                            <th class="text-center">الدرجة النهائية</th>
                            <th class="text-center">النسبة المئوية</th>
                            <th class="text-center">الوقت المستغرق</th>
                            <th class="text-center">تاريخ التقديم</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                            @php 
                                $percentage = ($result->score / ($result->total ?: 1)) * 100;
                                $statusClass = $percentage >= 50 ? 'success' : 'danger';
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $result->student_name ?? 'طالب غير مسجل' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusClass }}-light text-{{ $statusClass }} fs-6 px-3">
                                        {{ $result->score }} / {{ $result->total }}
                                    </span>
                                </td>
                                <td class="text-center" style="min-width: 150px;">
                                    <div class="progress rounded-pill" style="height: 10px;">
                                        <div class="progress-bar bg-{{ $statusClass }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ round($percentage) }}%</small>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted"><i class="far fa-clock me-1"></i> {{ $result->time_spent }} دقيقة</span>
                                </td>
                                <td class="text-center small text-muted">
                                    {{ \Carbon\Carbon::parse($result->created_at)->format('Y/m/d H:i') }}
                                </td>
                                <td class="text-center">
                                    @if($percentage >= 50)
                                        <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i> ناجح</span>
                                    @else
                                        <span class="text-danger small fw-bold"><i class="fas fa-times-circle me-1"></i> راسب</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                    لا توجد نتائج مسجلة لهذا الاختبار بعد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- فورم الحذف المخفي --}}
<form id="delete-form-{{ $quiz->id }}" action="{{ route('teacher.quizzes.delete', $quiz->id) }}" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

<style>
    /* تحسينات الألوان */
    .bg-success-light { background-color: #d1e7dd; color: #0f5132; }
    .bg-danger-light { background-color: #f8d7da; color: #842029; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    
    @media print {
        .btn-group, .d-print-none { display: none !important; }
        body { background: white !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>

<script>
function confirmDelete(id) {
    if(confirm('تنبيه: سيتم حذف الاختبار وجميع نتائج الطلاب المرتبطة به. هل أنت متأكد؟')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endsection