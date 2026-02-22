@extends('layouts.parent')

@section('title', 'سلوك الابناء')

@section('content')

    <div class="page-header-card p-4 text-white shadow mb-4">
        <h3 class="mb-1">تقارير السلوك</h3>
        <p class="mb-0">
            متابعة السلوك العام للابناء وتنبيهات المرشد والمعلمين.
        </p>
    </div>

    @if(isset($behaviourReports) && $behaviourReports->count())
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>الصف</th>
                            <th>التاريخ</th>
                            <th>تقييم السلوك</th>
                            <th>ملاحظات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($behaviourReports as $report)
                            <tr>
                                <td>{{ $report->child_name ?? '-' }}</td>
                                <td>{{ $report->class_name ?? '-' }}</td>
                                <td>{{ $report->date ?? '-' }}</td>
                                <td>
                                    @php
                                        $level = $report->level ?? 'غير محدد';
                                    @endphp
                                    <span class="badge
                                        @if($level === 'ممتاز') bg-success
                                        @elseif($level === 'جيد جدا') bg-primary
                                        @elseif($level === 'جيد') bg-info text-dark
                                        @elseif($level === 'يحتاج متابعة') bg-warning text-dark
                                        @else bg-secondary
                                        @endif">
                                        {{ $level }}
                                    </span>
                                </td>
                                <td>{{ $report->note ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    @else
        <div class="alert alert-info">
            لا توجد تقارير سلوك مسجلة حاليا.
        </div>
    @endif

@endsection
