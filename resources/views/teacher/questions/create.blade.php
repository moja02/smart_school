@extends('layouts.teacher')
@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold m-0">إضافة سؤال جديد ({{ $subject->name }} - {{ $class->name }})</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form action="{{ route('teacher.questions.store', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>اختر الدرس</label>
                    <select name="lesson_id" class="form-select">
                        <option value="">-- اختر --</option>
                        @foreach($lessons as $l) <option value="{{ $l->id }}">{{ $l->title }}</option> @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>أو درس جديد</label>
                    <input type="text" name="lesson_name" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label>السؤال</label>
                <textarea name="content" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label>النوع</label>
                <select name="type" class="form-select" id="qType" onchange="toggle()">
                    <option value="true_false">صح/خطأ</option>
                    <option value="multiple_choice">اختيار من متعدد</option>
                </select>
            </div>
            <div id="opts" style="display:none" class="mb-3 bg-light p-3">
                <label>الخيارات</label>
                <div class="row g-2">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="خيار 1">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="خيار 2">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="خيار 3">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="خيار 4">
                </div>
            </div>
            <div class="mb-3">
                <label>الإجابة الصحيحة</label>
                <input type="text" name="correct_answer" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">حفظ</button>
        </form>
    </div>
</div>
<script>
function toggle(){ document.getElementById('opts').style.display = document.getElementById('qType').value == 'multiple_choice' ? 'block' : 'none'; }
</script>
@endsection