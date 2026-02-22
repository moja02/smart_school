@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i> تعديل السؤال</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.question.update', $question->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- نص السؤال --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">نص السؤال</label>
                    <textarea name="content" class="form-control" rows="3" required>{{ $question->content }}</textarea>
                </div>

                {{-- الخيارات (تظهر فقط للاختيار من متعدد) --}}
                @if($question->type == 'multiple_choice')
                    <div class="mb-4">
                        <label class="form-label fw-bold">الخيارات</label>
                        @foreach($question->options as $index => $option)
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light">{{ $index + 1 }}</span>
                            <input type="text" name="options[]" class="form-control" value="{{ $option }}" required>
                        </div>
                        @endforeach
                        {{-- خيار إضافي فارغ إذا أراد الزيادة --}}
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light">+</span>
                            <input type="text" name="options[]" class="form-control" placeholder="خيار إضافي (اختياري)">
                        </div>
                    </div>
                @endif

                {{-- الإجابة الصحيحة --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-success">الإجابة الصحيحة</label>
                    @if($question->type == 'true_false')
                        <select name="correct_answer" class="form-select">
                            <option value="صح" {{ $question->correct_answer == 'صح' ? 'selected' : '' }}>صح ✅</option>
                            <option value="خطأ" {{ $question->correct_answer == 'خطأ' ? 'selected' : '' }}>خطأ ❌</option>
                        </select>
                    @else
                        <input type="text" name="correct_answer" class="form-control" value="{{ $question->correct_answer }}" placeholder="انسخ الإجابة الصحيحة هنا تماماً كما في الخيارات" required>
                        <div class="form-text">يجب أن تكون مطابقة تماماً لأحد الخيارات أعلاه.</div>
                    @endif
                </div>

                {{-- ملاحظات التصحيح --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted">ملاحظات تظهر للطالب بعد الحل (اختياري)</label>
                    <input type="text" name="feedback" class="form-control" value="{{ $question->feedback }}">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill">تحديث السؤال</button>
                    <button type="button" onclick="history.back()" class="btn btn-secondary px-4 rounded-pill">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection