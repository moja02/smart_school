@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-trophy me-2"></i> تقرير أوائل الطلبة</h2>
        <a href="{{ route('admin.reports.advanced') }}" class="btn btn-secondary">الرجوع</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.top_students') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="class_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- اختر الفصل --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} {{ $class->section ? '- '.$class->section : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedClass && isset($topStudents) && $topStudents->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>الترتيب</th>
                                <th>اسم الطالب</th>
                                <th>المجموع الكلي</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topStudents as $index => $student)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <span class="badge bg-warning fs-6"><i class="fas fa-medal"></i> الأول</span>
                                        @elseif($index == 1)
                                            <span class="badge bg-secondary fs-6"><i class="fas fa-medal"></i> الثاني</span>
                                        @elseif($index == 2)
                                            <span class="badge bg-danger fs-6"><i class="fas fa-medal"></i> الثالث</span>
                                        @else
                                            <span class="badge bg-primary">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ $student->name }}</td>
                                    <td>{{ $student->total_score }} درجة</td>
                                    <td>
                                        @php
                                            $percent = $student->total_score > 0 ? min(100, round(($student->total_score / 1000) * 100, 2)) : 0; // Assume 1000 max for now
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $percent >= 90 ? 'bg-success' : ($percent >= 75 ? 'bg-info' : 'bg-warning') }}" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($selectedClass)
        <div class="alert alert-warning text-center">لا يوجد طلاب أو درجات مسجلة في هذا الفصل.</div>
    @else
        <div class="alert alert-info text-center">يرجى اختيار الفصل لعرض الأوائل.</div>
    @endif
</div>
@endsection
