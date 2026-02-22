@extends('layouts.admin')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card page-header-card mb-4 text-center">
                <h3 class="fw-bold m-0">تعديل بيانات المستخدم ✏️</h3>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">نوع الحساب (الصلاحية)</label>
                            <select name="role" class="form-select">
                                <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>طالب</option>
                                <option value="teacher" {{ $user->role == 'teacher' ? 'selected' : '' }}>معلم</option>
                                <option value="parent" {{ $user->role == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary rounded-pill px-4">إلغاء</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">حفظ التغييرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection