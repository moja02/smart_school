@extends('layouts.parent')

@section('title', 'درجات الابناء')

@section('content')

    <div class="page-header-card p-4 text-white shadow mb-4">
        <h3 class="mb-1">درجات الابناء</h3>
        <p class="mb-0">عرض الدرجات في جميع المواد والفصول الدراسية.</p>
    </div>

    @if(isset($children) && $children->count())
        @foreach($children as $child)
            <div class="card mb-4 shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <strong>الطالب:</strong> {{ $child->name ?? 'غير معروف' }}<br>
                        <small class="text-muted">
                            الصف: {{ $child->class_name ?? 'غير محدد' }}
                        </small>
                    </div>
                </div>

                <div class="card-body">
                    @if(isset($child->grades) && $child->grades->count())
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>المادة</th>
                                    <th>الفصل</th>
                                    <th>الدرجة</th>
                                    <th>من</th>
                                    <th>النسبة المئوية</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($child->grades as $grade)
                                    @php
                                        $percent = $grade->max_score > 0
                                            ? round(($grade->score / $grade->max_score) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $grade->subject_name ?? 'غير محدد' }}</td>
                                        <td>{{ $grade->term ?? '-' }}</td>
                                        <td>{{ $grade->score }}</td>
                                        <td>{{ $grade->max_score }}</td>
                                        <td>
                                            <span class="badge
                                                @if($percent >= 85) bg-success
                                                @elseif($percent >= 60) bg-primary
                                                @elseif($percent >= 50) bg-warning text-dark
                                                @else bg-danger
                                                @endif">
                                                {{ $percent }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">لم يتم تسجيل درجات لهذا الطالب حتى الان.</p>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">
            لا يوجد ابناء مرتبطون بهذا الحساب حتى الان، او لا توجد درجات مسجلة.
        </div>
    @endif

@endsection
