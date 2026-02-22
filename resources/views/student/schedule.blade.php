@extends('layouts.student')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">جدولي الدراسي 📅</h3>
            <p class="mb-0 opacity-75">جدول الحصص الأسبوعي.</p>
        </div>
        <i class="fas fa-table fa-4x opacity-25"></i>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th style="width: 15%">اليوم / الحصة</th>
                        @foreach($periods as $period)
                            <th>الحصة {{ $period }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                    <tr>
                        <td class="fw-bold bg-light">{{ $day }}</td>
                        @foreach($periods as $period)
                            <td>
                                @php
                                    $session = isset($schedules[$day]) ? $schedules[$day]->where('period', $period)->first() : null;
                                @endphp

                                @if($session)
                                    <div class="p-2 rounded bg-info bg-opacity-10 text-primary fw-bold">
                                        {{ $session->subject->name }}
                                    </div>
                                @else
                                    <span class="text-muted small">--</span>
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
@endsection