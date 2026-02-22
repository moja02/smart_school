@extends('layouts.admin')

@section('content')

<style>
    @media print {
        @page { size: landscape; margin: 10mm; }
        .no-print, .sidebar, .navbar { display: none !important; }
        .main-content { margin: 0; width: 100%; }
        table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        .page-break { page-break-before: always; } /* فاصل صفحات */
        .bg-light, .badge { background-color: white !important; color: black !important; border: none; }
    }
</style>

<div class="card shadow mb-4 no-print">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <div>
            <h5 class="m-0 fw-bold text-primary"><i class="fas fa-layer-group me-2"></i> تقرير المرحلة: {{ $grade->name }}</h5>
            <small class="text-muted">مقسم حسب الصفوف الدراسية</small>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fas fa-print me-1"></i> طباعة الكل
            </button>
            <a href="{{ route('admin.classes') }}" class="btn btn-secondary btn-sm rounded-pill">عودة</a>
        </div>
    </div>
</div>

{{-- ✅ حلقة تكرار لكل صف دراسي (مجموعة) --}}
@foreach($students as $className => $classStudents)

    <div class="card shadow border-0 mb-5 {{ !$loop->first ? 'page-break' : '' }}">
        <div class="card-header bg-dark text-white text-center py-2">
            <h4 class="m-0 fw-bold">{{ $className }}</h4> <small>{{ $grade->name }} - العام الدراسي {{ date('Y') }}</small>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" style="vertical-align: middle; width: 50px;">#</th>
                            <th rowspan="2" style="vertical-align: middle; min-width: 180px;">اسم الطالب</th>
                            <th rowspan="2" style="vertical-align: middle; width: 80px;">الشعبة</th>
                            
                            @if($subjects->count() > 0)
                                <th colspan="{{ $subjects->count() }}">المواد الدراسية</th>
                            @else
                                <th rowspan="2">المواد</th>
                            @endif

                            <th rowspan="2" style="vertical-align: middle; background-color: #f8f9fa;">المجموع</th>
                            <th rowspan="2" style="vertical-align: middle;">الغياب</th>
                        </tr>
                        <tr>
                            @forelse($subjects as $subject)
                                <th class="small">{{ $subject->name }}</th>
                            @empty
                                <th>-</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classStudents as $student)
                        <tr class="{{ $loop->iteration <= 3 ? 'bg-warning bg-opacity-10' : '' }}">
                            
                            {{-- الترتيب داخل هذا الصف --}}
                            <td class="fw-bold">
                                {{ $loop->iteration }}
                                @if($loop->iteration == 1) 🥇 @endif
                            </td>

                            <td class="text-start fw-bold">{{ $student->name }}</td>
                            
                            {{-- الشعبة --}}
                            <td><span class="badge bg-light text-dark border">{{ $student->studentProfile->schoolClass->section }}</span></td>

                            {{-- الدرجات --}}
                            @foreach($subjects as $subject)
                                @php
                                    $mark = $student->marks->where('subject_id', $subject->id)->first();
                                    $score = $mark ? $mark->score : 0;
                                @endphp
                                <td class="{{ $score < 50 ? 'text-danger fw-bold' : '' }}">{{ $score }}</td>
                            @endforeach

                            @if($subjects->isEmpty()) <td>-</td> @endif

                            <td class="fw-bold bg-light">{{ $student->total_score }}</td>
                            <td>{{ $student->absence_days > 0 ? $student->absence_days : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $subjects->count() + 5 }}" class="text-muted">لا يوجد طلاب في هذا الصف.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- إحصائية سريعة أسفل كل جدول --}}
            <div class="mt-2 text-muted small no-print">
                <i class="fas fa-check-circle me-1"></i> عدد طلاب {{ $className }}: {{ $classStudents->count() }} طالب
            </div>
        </div>
    </div>

@endforeach

@if($students->isEmpty())
    <div class="alert alert-info text-center m-5">
        <i class="fas fa-info-circle fa-2x mb-3"></i><br>
        لا توجد بيانات لعرضها في هذه المرحلة.
    </div>
@endif

@endsection