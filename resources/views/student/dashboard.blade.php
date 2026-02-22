@extends('layouts.student')
@section('content')

<div class="container py-5">
    
    {{-- هيدر الصفحة --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark">لوحة الطالب 🎓</h2>
            <p class="text-muted">أهلاً بك، <b>{{ $user->name }}</b> | فصل: {{ $class->grade?->name ?? 'غير محدد' }} - {{ $class->section }}</p>
        </div>
        <div class="bg-white p-3 rounded-circle shadow-sm text-primary">
            <i class="fas fa-user-graduate fa-2x"></i>
        </div>
    </div>

    {{-- شبكة المواد --}}
    <div class="row">
        @forelse($subjects as $subject)
        <div class="col-md-6 col-lg-4 mb-4">
            {{-- كرت المادة --}}
            <div class="card h-100 border-0 shadow-sm hover-card overflow-hidden">
                <div class="card-body p-4 position-relative">
                    {{-- زخرفة خلفية --}}
                    <i class="fas fa-book position-absolute opacity-10" style="font-size: 8rem; right: -20px; bottom: -20px; color: var(--bs-primary);"></i>
                    
                    <h5 class="fw-bold text-dark mb-1">{{ $subject->name }}</h5>
                    <p class="text-muted small mb-4"><i class="fas fa-chalkboard-teacher me-1"></i> {{ $subject->teacher_name }}</p>

                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <div>
                            {{-- عرض سريع لعدد الامتحانات القادمة --}}
                            @if($subject->upcoming_exams->count() > 0)
                                <span class="badge bg-warning text-dark mb-2">
                                    <i class="fas fa-clock me-1"></i> {{ $subject->upcoming_exams->count() }} امتحان قادم
                                </span>
                            @else
                                <span class="badge bg-light text-muted mb-2">لا توجد امتحانات</span>
                            @endif
                        </div>
                        
                        {{-- زر التفاصيل --}}
                        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#subjectModal{{ $subject->id }}">
                            التفاصيل <i class="fas fa-arrow-left ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============================== --}}
            {{-- نافذة التفاصيل (MODAL) --}}
            {{-- ============================== --}}
            <div class="modal fade" id="subjectModal{{ $subject->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold">
                                <i class="fas fa-book-open me-2"></i> {{ $subject->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <div class="row g-4">
                                
                                {{-- العمود الأيمن: الدرجات --}}
                                <div class="col-md-6">
                                    <div class="bg-white p-3 rounded shadow-sm h-100">
                                        <h6 class="fw-bold text-success border-bottom pb-2 mb-3">
                                            <i class="fas fa-chart-line me-2"></i> درجاتي وسجل العلامات
                                        </h6>
                                        @if($subject->my_grades->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>الامتحان</th>
                                                            <th class="text-center">الدرجة</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subject->my_grades as $grade)
                                                        <tr>
                                                            <td>{{ $grade->title }}</td>
                                                            <td class="text-center">
                                                                <span class="fw-bold {{ $grade->score >= ($grade->max_score/2) ? 'text-success' : 'text-danger' }}">
                                                                    {{ $grade->score }} / {{ $grade->max_score }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-clipboard me-1"></i> لا توجد درجات مرصودة بعد.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- العمود الأيسر: المواعيد --}}
                                <div class="col-md-6">
                                    <div class="bg-white p-3 rounded shadow-sm h-100">
                                        <h6 class="fw-bold text-warning text-dark border-bottom pb-2 mb-3">
                                            <i class="fas fa-calendar-alt me-2"></i> الامتحانات القادمة
                                        </h6>
                                        @if($subject->upcoming_exams->count() > 0)
                                            <ul class="list-group list-group-flush">
                                                @foreach($subject->upcoming_exams as $exam)
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <div>
                                                        <span class="fw-bold d-block text-dark">{{ $exam->title }}</span>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($exam->exam_date)->locale('ar')->diffForHumans() }}</small>
                                                    </div>
                                                    <span class="badge bg-warning text-dark rounded-pill">
                                                        {{ \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') }}
                                                    </span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-coffee me-1"></i> استراحة! لا توجد امتحانات قريبة.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- نهاية الـ Modal --}}

        </div>
        @empty
        <div class="col-12 text-center">
            <div class="alert alert-info">لا توجد مواد دراسية لعرضها حالياً.</div>
        </div>
        @endforelse
    </div>
</div>

{{-- CSS بسيط للأنيميشن --}}
<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .modal-content {
        overflow: hidden;
    }
</style>

@endsection