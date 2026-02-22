@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i> تعديل بيانات الفصل</h6>
            <a href="{{ route('admin.classes') }}" class="btn btn-secondary btn-sm rounded-pill">
                <i class="fas fa-arrow-right"></i> عودة للقائمة
            </a>
        </div>
        <div class="card-body">
            
            <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- المرحلة الدراسية --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">المرحلة الدراسية <span class="text-danger">*</span></label>
                        <select name="grade_id" class="form-select" required>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ $class->grade_id == $grade->id ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- اسم الصف --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">اسم الصف (السنة) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $class->name) }}" required>
                        <small class="text-muted">مثال: الصف الأول الإعدادي</small>
                    </div>

                    {{-- الشعبة --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">الشعبة / القسم <span class="text-danger">*</span></label>
                        <input type="text" name="section" class="form-control" value="{{ old('section', $class->section) }}" required>
                        <small class="text-muted">مثال: أ، ب، A، B</small>
                    </div>
                </div>

                <hr>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection