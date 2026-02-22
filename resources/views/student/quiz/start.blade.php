@extends('layouts.student')

@section('content')
<div class="container py-4">
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="m-0 fw-bold"><i class="fas fa-pencil-alt me-2"></i> اختبار درس: {{ $lesson->title }}</h4>
            <span class="small opacity-75">المادة: {{ $lesson->subject->name ?? '' }}</span>
        </div>
        <div class="card-body p-4">
            
            <form action="{{ route('student.quiz.submit', $lesson->id) }}" method="POST">
                @csrf
                
                @foreach($lesson->questions as $index => $question)
                <div class="card mb-4 border-0 shadow-sm bg-light">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark">
                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                            {{ $question->content }}
                        </h5>

                        <div class="ms-4 mt-3">
                            {{-- النوع: اختيار من متعدد --}}
                            @if($question->type == 'multiple_choice' && is_array($question->options))
                                @foreach($question->options as $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="q_{{ $question->id }}" id="opt_{{ $question->id }}_{{ $loop->index }}" value="{{ $option }}" required>
                                    <label class="form-check-label fs-6" for="opt_{{ $question->id }}_{{ $loop->index }}">
                                        {{ $option }}
                                    </label>
                                </div>
                                @endforeach
                            
                            {{-- النوع: صح أو خطأ --}}
                            @elseif($question->type == 'true_false')
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="q_{{ $question->id }}" id="tf_true_{{ $question->id }}" value="صح" required>
                                    <label class="form-check-label text-success fw-bold" for="tf_true_{{ $question->id }}">صح ✅</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q_{{ $question->id }}" id="tf_false_{{ $question->id }}" value="خطأ" required>
                                    <label class="form-check-label text-danger fw-bold" for="tf_false_{{ $question->id }}">خطأ ❌</label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow">
                        <i class="fas fa-check-circle me-2"></i> تسليم الإجابات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection