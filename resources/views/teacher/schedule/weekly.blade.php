@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    
    {{-- ترويسة الصفحة --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-white"><i class="fas fa-calendar-alt me-2 text-warning"></i> جدولي الدراسي</h2>
                <p class="mb-0 opacity-75">هذا الجدول يوضح توزيع الحصص الخاصة بك خلال أيام الأسبوع.</p>
            </div>
            <div class="d-none d-md-block opacity-25">
                <i class="fas fa-table fa-4x text-white"></i>
            </div>
        </div>
    </div>

    {{-- جدول المصفوفة --}}
    <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
            <h5 class="fw-bold text-primary mb-0">
                <i class="fas fa-chalkboard-teacher me-2 text-secondary opacity-50"></i> 
                الجدول الأسبوعي للأستاذ: {{ $teacher->name }}
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle mb-0">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th class="py-3 bg-secondary text-white border-0">اليوم / الحصة</th>
                            @foreach($periods as $period) 
                                <th class="bg-light border-bottom-0">الحصة {{ $period }}</th> 
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            <tr>
                                <td class="fw-bold bg-light border-end text-dark">{{ $day }}</td>
                                @foreach($periods as $period)
                                    @php
                                        // 💡 نفس الكود من لوحة الإدارة بالضبط للبحث في المصفوفة
                                        $schedule = $teacher->schedules->where('day', $day)->where('period', $period)->first();
                                    @endphp
                                    <td style="width: 13%;">
                                        @if($schedule)
                                            <div class="p-2 bg-primary bg-opacity-10 rounded shadow-sm border border-primary border-opacity-25">
                                                <div class="fw-bold text-primary mb-1">{{ $schedule->subject->name ?? 'مادة' }}</div>
                                                <span class="badge bg-dark rounded-pill shadow-sm">
                                                    <i class="fas fa-users me-1"></i> {{ $schedule->schoolClass->name ?? $schedule->schoolClass->section ?? 'فصل' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-black-50 opacity-25">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection