@extends('layouts.parent')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">سجل الحضور والغياب 📅</h3>
            <p class="mb-0 opacity-75">متابعة انضباط وحضور أبنائك يومياً.</p>
        </div>
        <i class="fas fa-calendar-check fa-4x opacity-25"></i>
    </div>
</div>

@if($children->count() > 0)
    <div class="card shadow border-0">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <ul class="nav nav-tabs card-header-tabs" id="childrenTabs" role="tablist">
                @foreach($children as $index => $child)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index == 0 ? 'active' : '' }} fw-bold" 
                                id="tab-{{ $child->id }}" 
                                data-bs-toggle="tab" 
                                data-bs-target="#content-{{ $child->id }}" 
                                type="button" role="tab">
                            <i class="fas fa-user-graduate me-2"></i> {{ $child->user->name }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        
        <div class="card-body bg-white">
            <div class="tab-content" id="childrenTabsContent">
                @foreach($children as $index => $child)
                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="content-{{ $child->id }}" role="tabpanel">
                        
                        @php
                            $records = $attendanceData[$child->id];
                            $absentCount = $records->where('status', 0)->count();
                        @endphp

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-danger text-white border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h2 class="fw-bold m-0">{{ $absentCount }}</h2>
                                        <small>أيام الغياب</small>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                            <td>{{ \Carbon\Carbon::parse($record->attendance_date)->translatedFormat('l') }}</td>
                                            <td class="text-center">
                                                @if($record->status == 1)
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                        <i class="fas fa-check-circle me-1"></i> حاضر
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                                        <i class="fas fa-times-circle me-1"></i> غائب
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                                <p>لم يتم تسجيل أي بيانات حضور لهذا الطالب بعد.</p>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">لا يوجد أبناء مرتبطين بهذا الحساب.</div>
@endif

@endsection