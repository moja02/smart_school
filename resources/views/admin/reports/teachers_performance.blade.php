@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-chalkboard-teacher me-2"></i> تقرير أداء المعلمين</h2>
        <a href="{{ route('admin.reports.advanced') }}" class="btn btn-secondary">الرجوع</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>المعلم</th>
                            <th>عدد الفصول التي يدرسها</th>
                            <th>متوسط درجات الطلاب</th>
                            <th>التقييم الأكاديمي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teacherStats as $stat)
                            <tr>
                                <td class="fw-bold">{{ $stat['name'] }}</td>
                                <td>{{ $stat['sections_count'] }}</td>
                                <td>{{ $stat['avg_student_score'] }}</td>
                                <td>
                                    @if($stat['avg_student_score'] >= 85)
                                        <span class="badge bg-success">ممتاز</span>
                                    @elseif($stat['avg_student_score'] >= 75)
                                        <span class="badge bg-primary">جيد جداً</span>
                                    @elseif($stat['avg_student_score'] >= 60)
                                        <span class="badge bg-warning">جيد</span>
                                    @elseif($stat['sections_count'] == 0)
                                        <span class="badge bg-secondary">غير معين</span>
                                    @else
                                        <span class="badge bg-danger">ضعيف</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">لا يوجد معلمين مضافين.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
