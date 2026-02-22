@extends('layouts.student')

@section('title', 'سجل الدرجات')

@section('content')

<div class="page-header mb-4">
    <h3>سجل الدرجات والنتائج 📊</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
        <li class="breadcrumb-item active">/ الدرجات</li>
    </ul>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المادة الدراسية</th>
                        <th>الفصل الدراسي</th>
                        <th>الدرجة المكتسبة</th>
                        <th>الدرجة العظمى</th>
                        <th>التقدير</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $grade)
                    <tr>
                        <td class="fw-bold">{{ $grade->subject }}</td>
                        
                        <td>{{ $grade->term }}</td>
                        
                        <td>
                            @if($grade->total_score >= 50)
                                <span class="text-success fw-bold">{{ $grade->total_score }}</span>
                            @else
                                <span class="text-danger fw-bold">{{ $grade->total_score }}</span>
                            @endif
                        </td>
                        
                        <td>{{ $grade->max_score }}</td>
                        
                        <td>
                            @if($grade->total_score >= 90) <span class="badge bg-success">ممتاز</span>
                            @elseif($grade->total_score >= 75) <span class="badge bg-info">جيد جداً</span>
                            @elseif($grade->total_score >= 60) <span class="badge bg-warning">جيد</span>
                            @elseif($grade->total_score >= 50) <span class="badge bg-secondary">مقبول</span>
                            @else <span class="badge bg-danger">راسب</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">لا توجد درجات مرصودة حتى الآن.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
