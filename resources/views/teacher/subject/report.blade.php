@extends('layouts.teacher')

@section('content')
<div class="container-fluid py-4">
    
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i> تقرير المادة: {{ $subject->name }}</h5>
                <span class="small opacity-75">الفصل: {{ $class->name }}</span>
            </div>
            <div>
        <a href="{{ route('teacher.subject.report.print', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" target="_blank" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold me-2">
            <i class="fas fa-print me-1"></i> طباعة التقرير
        </a>

        <a href="{{ route('teacher.class', $class->id) }}" class="btn btn-light btn-sm rounded-pill px-3 text-primary fw-bold">
            <i class="fas fa-arrow-right me-1"></i> عودة
        </a>
    </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="bg-light text-nowrap">
                        <tr>
                            <th rowspan="2" class="align-middle bg-white">#</th>
                            <th rowspan="2" class="align-middle bg-white text-start" style="min-width: 200px;">اسم الطالب</th>
                            
                            {{-- أعمدة التقييمات الرسمية --}}
                            @if($assessments->count() > 0)
                                <th colspan="{{ $assessments->count() }}" class="bg-light">التقييمات الرسمية</th>
                            @endif
                            
                            {{-- عمود الاختبارات الذاتية --}}
                            <th rowspan="2" class="align-middle bg-white">الاختبارات الذاتية<br><small class="text-muted">(دروس تم اجتيازها)</small></th>
                            
                            <th rowspan="2" class="align-middle bg-light fw-bold">المجموع</th>
                        </tr>
                        <tr>
                            {{-- أسماء التقييمات --}}
                            @foreach($assessments as $assessment)
                                <th class="small">{{ $assessment->title }} <br> <span class="text-muted">({{ $assessment->max_score }})</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->students as $index => $student)
                        <tr>
                            <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                            <td class="text-start fw-bold">{{ $student->user->name }}</td>

                            @php $totalScore = 0; @endphp

                            {{-- عرض درجات التقييمات --}}
                            @forelse($assessments as $assessment)
                                @php
                                    // البحث عن درجة الطالب في المتغير marks القادم من الكنترولر
                                    $mark = $marks->where('student_id', $student->id)
                                                  ->where('assessment_id', $assessment->id)
                                                  ->first();
                                    
                                    if($mark) $totalScore += $mark->score;
                                @endphp
                                <td>
                                    @if($mark)
                                        <span class="fw-bold {{ $mark->score < ($assessment->max_score/2) ? 'text-danger' : 'text-dark' }}">
                                            {{ $mark->score }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            @empty
                                @if($assessments->count() == 0)
                                    <td class="text-muted small">لا يوجد تقييمات</td>
                                @endif
                            @endforelse

                            {{-- عرض ملخص الاختبارات الذاتية --}}
                            @php
                                // حساب عدد الاختبارات التي دخلها الطالب
                                $quizCount = $quizAttempts->where('student_id', $student->id)->count();
                                $totalLessons = $lessons->where('questions_count', '>', 0)->count(); // تقريبي
                            @endphp
                            <td>
                                @if($quizCount > 0)
                                    <span class="badge bg-success rounded-pill">{{ $quizCount }} اختبار</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- المجموع النهائي --}}
                            <td class="bg-light fw-bold text-primary">{{ $totalScore }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($class->students->count() == 0)
                <div class="text-center py-4 text-muted">
                    لا يوجد طلاب في هذا الفصل.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection