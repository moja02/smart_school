@extends('layouts.manager')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">تعيين مسؤول الدراسة (Admin) 🎓</h3>
            <p class="mb-0 opacity-75">إنشاء الحساب المسؤول عن إدارة النظام والجداول والدرجات.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-user-shield fa-4x opacity-25"></i>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        {{-- تنبيه بوجود مسؤول حالي --}}
        @if(isset($currentOfficer) && $currentOfficer)
            <div class="alert alert-warning d-flex align-items-center shadow-sm border-0 border-start border-4 border-warning mb-4">
                <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                <div>
                    <h6 class="fw-bold mb-1">انتبه: يوجد مسؤول حالي للنظام!</h6>
                    <p class="mb-0 small">الاسم: <strong>{{ $currentOfficer->name }}</strong> | البريد: {{ $currentOfficer->email }}</p>
                    <small class="text-muted">إنشاء حساب جديد قد يسبب تداخل في الصلاحيات إذا لم يتم حذف القديم.</small>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-pen-fancy me-2"></i> بيانات الحساب الجديد</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('manager.store_admin') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">الاسم الكامل</label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0" placeholder="مثلاً: أ. محمد الفيتوري" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">البريد الإلكتروني (لتسجيل الدخول)</label>
                        <input type="email" name="email" class="form-control form-control-lg bg-light border-0" placeholder="admin@school.com" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">كلمة المرور</label>
                        <input type="password" name="password" class="form-control form-control-lg bg-light border-0" placeholder="********" required>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">
                        <i class="fas fa-save me-2"></i> حفظ وتعيين الصلاحيات
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection