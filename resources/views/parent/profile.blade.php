@extends('layouts.parent')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <div class="card page-header-card mb-4 shadow">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0">إعدادات الحساب ⚙️</h3>
                <i class="fas fa-user-cog fa-3x opacity-25"></i>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                
                @if(session('success'))
                    <div class="alert alert-success rounded-3 shadow-sm mb-4">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('parent.updateProfile') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                        <input type="text" name="name" class="form-control form-control-lg" value="{{ Auth::user()->name }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                        <input type="email" class="form-control form-control-lg bg-light" value="{{ Auth::user()->email }}" disabled>
                        <small class="text-muted">لا يمكن تغيير البريد الإلكتروني. تواصل مع الإدارة للتغيير.</small>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-lock me-2"></i> تغيير كلمة المرور</h5>
                    <p class="text-muted small mb-3">اترك الحقول فارغة إذا كنت لا تريد تغيير كلمة المرور.</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm">
                            <i class="fas fa-save me-2"></i> حفظ التغييرات
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

@endsection
