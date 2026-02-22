@extends('layouts.student')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <div class="card page-header-card mb-4 shadow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">إعدادات الحساب ⚙️</h3>
                    <p class="mb-0 opacity-75">تحديث البيانات الشخصية وكلمة المرور.</p>
                </div>
                <i class="fas fa-user-cog fa-4x opacity-25"></i>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                
                @if(session('success'))
                    <div class="alert alert-success rounded-3 shadow-sm mb-4">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    </div>
                @endif

                {{-- عرض الأخطاء إن وجدت --}}
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('student.updateProfile') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">الاسم الكامل</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" class="form-control form-control-lg border-start-0" value="{{ Auth::user()->name }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control form-control-lg bg-light border-start-0" value="{{ Auth::user()->email }}" disabled>
                        </div>
                        <small class="text-muted ms-2"><i class="fas fa-info-circle"></i> لا يمكن تغيير البريد الإلكتروني. يرجى مراجعة الإدارة.</small>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-lock me-2"></i> تغيير كلمة المرور</h5>
                    <div class="alert alert-light border small text-muted">
                        اترك الحقول التالية فارغة إذا كنت لا تريد تغيير كلمة المرور.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control" placeholder="******">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="******">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow hover-scale">
                            <i class="fas fa-save me-2"></i> حفظ التغييرات
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

@endsection