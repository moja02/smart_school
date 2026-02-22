@extends('layouts.student')

@section('content')
<div class="container py-4">
    
    {{-- كارت النتيجة العلوية --}}
    <div class="card shadow border-0 mb-4 text-center">
        <div class="card-body py-5">
            @if($percentage >= 50)
                <div class="mb-3 text-success"><i class="fas fa-trophy fa-4x"></i></div>
                <h2 class="fw-bold text-success">عمل رائع!</h2>
            @else
                <div class="mb-3 text-warning"><i class="fas fa-check-circle fa-4x"></i></div>
                <h2 class="fw-bold text-dark">تم إتمام الاختبار</h2>
            @endif

            <h1 class="display-3 fw-bold my-3">{{ $score }} / {{ $total }}</h1>
            <h5 class="text-muted">النسبة المئوية: {{ $percentage }}%</h5>
            
            {{-- رسالة تأكيد الحفظ --}}
            <div class="alert alert-info d-inline-block px-4 py-2 mt-2 border-0 bg-light text-primary rounded-pill shadow-sm">
                <i class="fas fa-save me-1"></i> تم حفظ نتيجتك وإرسالها للمعلم بنجاح.
            </div>

            <div class="mt-5">
                {{-- زر العودة فقط --}}
                <a href="{{ route('student.subjects.show', $lesson->subject_id) }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    <i class="fas fa-arrow-right me-2"></i> عودة لصفحة الدرس
                </a>
            </div>
        </div>
    </div>

    {{-- تفاصيل الإجابات --}}
    <h4 class="fw-bold mb-3"><i class="fas fa-list-alt me-2"></i> تفاصيل إجاباتك:</h4>
    
    @foreach($results as $index => $res)
    <div class="card mb-3 border-0 shadow-sm border-start border-5 {{ $res['is_correct'] ? 'border-success' : 'border-danger' }}">
        <div class="card-body">
            
            {{-- ✅ التعديل هنا: عرض نص السؤال وبجانبه درجته --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-bold mb-0">
                    <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                    {{ $res['question'] }}
                </h6>
                <span class="badge bg-light text-dark border shadow-sm ms-2" style="min-width: 80px;">
                    {{ $res['question_score'] }} درجات
                </span>
            </div>
            
            <div class="row mt-2">
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">إجابتك:</p>
                    <div class="alert {{ $res['is_correct'] ? 'alert-success' : 'alert-danger' }} py-2 mb-0">
                        @if($res['is_correct']) 
                            <i class="fas fa-check me-1"></i> 
                            {{-- عرض الدرجة التي حصل عليها الطالب --}}
                            <span class="float-end fw-bold small text-success">+{{ $res['score_earned'] }}</span>
                        @else 
                            <i class="fas fa-times me-1"></i> 
                            <span class="float-end fw-bold small text-danger">0</span>
                        @endif
                        {{ $res['user_answer'] }}
                    </div>
                </div>
                
                @if(!$res['is_correct'])
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">الإجابة الصحيحة:</p>
                    <div class="alert alert-success py-2 mb-0">
                        <i class="fas fa-check-double me-1"></i> {{ $res['correct_answer'] }}
                    </div>
                </div>
                @endif
            </div>

            @if($res['explanation'])
                <div class="mt-3 bg-light p-2 rounded small text-muted">
                    <i class="fas fa-lightbulb text-warning me-1"></i> <strong>ملاحظة:</strong> {{ $res['explanation'] }}
                </div>
            @endif
        </div>
    </div>
    @endforeach

</div>
@endsection