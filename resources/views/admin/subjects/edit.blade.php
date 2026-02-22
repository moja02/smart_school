@extends('layouts.admin')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="card page-header-card mb-4 text-center shadow">
                <h3 class="fw-bold m-0">تعديل اسم المادة ✏️</h3>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold">اسم المادة</label>
                            <input type="text" name="name" class="form-control form-control-lg" value="{{ $subject->name }}" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.subjects') }}" class="btn btn-secondary rounded-pill px-4">إلغاء</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection