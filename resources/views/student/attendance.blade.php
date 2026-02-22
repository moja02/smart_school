@extends('layouts.student')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">سجل الحضور والغياب 📋</h3>
            <p class="mb-0 opacity-75">متابعة أيام الحضور والغياب.</p>
        </div>
        <i class="fas fa-user-clock fa-4x opacity-25"></i>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold m-0">{{ $presentCount }}</h2>
                    <small>أيام الحضور</small>
                </div>
                <i class="fas fa-check-circle fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-danger text-white shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold m-0">{{ $absentCount }}</h2>
                    <small>أيام الغياب</small>
                </div>
                <i class="fas fa-times-circle fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body">
        @if($records->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            <td>{{ $record->attendance_date }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->attendance_date)->locale('ar')->translatedFormat('l') }}</td>
                            <td class="text-center">
                                @if($record->status == 1)
                                    <span class="badge bg-success rounded-pill px-3">حاضر</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3">غائب</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <p>لم يتم تسجيل أي بيانات حضور حتى الآن.</p>
            </div>
        @endif
    </div>
</div>
@endsection