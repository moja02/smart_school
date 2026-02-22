@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i> تعديل الدرس: {{ $lesson->title }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.lesson.update', $lesson->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">عنوان الدرس</label>
                    <input type="text" name="title" class="form-control" value="{{ $lesson->title }}" required>
                </div>

                {{-- يمكنك إضافة حقل رفع ملف جديد هنا إذا أردت استبدال الملف القديم --}}

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary px-4">حفظ التعديلات</button>
                    <button type="button" onclick="history.back()" class="btn btn-secondary px-4">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection