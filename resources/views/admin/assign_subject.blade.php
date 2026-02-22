@extends('layouts.master') @section('content')
<div class="page-header">
    <h3 class="page-title">توزيع المواد على المعلمين 👨‍🏫</h3>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('admin.storeAssign') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">اختر المعلم</label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">-- اختر --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">اختر المادة</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">-- اختر --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">تأكد أنك أضفت مواد في جدول subjects أولاً</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">اختر الفصل الدراسي</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">-- اختر --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">حفظ التعيين</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection