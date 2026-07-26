@extends('layouts.student')
@section('title', 'حل الاختبار')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold">{{ $exam->title }}</h3>
                        <span class="badge bg-white text-primary fs-6">{{ $exam->subject->name }}</span>
                    </div>
                    <div class="text-center bg-white text-danger px-4 py-2 rounded-3 shadow-sm">
                        <small class="d-block fw-bold mb-1 text-muted">الوقت المتبقي</small>
                        <h3 class="mb-0 fw-bold" id="timer">{{ $exam->duration_minutes }}:00</h3>
                    </div>
                </div>
                
                <div class="card-body p-4 bg-light">
                    <form action="{{ route('student.online_exams.submit', $exam->id) }}" method="POST" id="examForm">
                        @csrf
                        
                        @foreach($exam->questions as $index => $q)
                        <div class="card mb-4 border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4" style="line-height: 1.6;">
                                    <span class="text-primary me-2">{{ $index + 1 }}.</span> {{ $q->question_text }}
                                </h5>
                                
                                <div class="row g-3">
                                    @foreach($q->options as $optKey => $optValue)
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="q_{{ $q->id }}" id="q_{{ $q->id }}_{{ $optKey }}" value="{{ $optValue }}" required>
                                        <label class="btn btn-outline-primary w-100 text-start p-3 rounded-3" for="q_{{ $q->id }}_{{ $optKey }}">
                                            {{ $optValue }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold rounded-pill shadow" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i> تسليم الاختبار
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
</style>

<script>
    // مؤقت الاختبار
    let timeRemaining = {{ $exam->duration_minutes }} * 60;
    const timerDisplay = document.getElementById('timer');
    const examForm = document.getElementById('examForm');

    const countdown = setInterval(() => {
        timeRemaining--;
        let minutes = Math.floor(timeRemaining / 60);
        let seconds = timeRemaining % 60;
        
        timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (timeRemaining <= 60) {
            timerDisplay.parentElement.classList.add('bg-danger', 'text-white');
            timerDisplay.parentElement.classList.remove('bg-white', 'text-danger');
            timerDisplay.classList.add('text-white');
        }

        if (timeRemaining <= 0) {
            clearInterval(countdown);
            alert('انتهى الوقت! سيتم تسليم إجاباتك الآن تلقائياً.');
            examForm.submit();
        }
    }, 1000);
</script>
@endsection
