@extends('layouts.system')

@section('content')
<div class="container-fluid py-4">
    
    {{-- 1. ترويسة الصفحة --}}
    <div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #2d3436, #000000);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1 text-white"><i class="fas fa-user-plus me-2 text-warning"></i> إنشاء حساب جديد</h2>
                <p class="mb-0 text-white opacity-75">
                    أنت الآن تقوم بإنشاء حساب تابع لمدرسة: 
                    <strong class="text-warning">{{ $school->name }}</strong>
                </p>
            </div>
            <div class="text-end d-flex gap-2">
                <a href="{{ route('system.schools.index') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm text-dark">
                    <i class="fas fa-arrow-right me-2"></i> العودة لقائمة المدارس
                </a>
            </div>
        </div>
    </div>

    {{-- عرض أخطاء التحقق إن وجدت --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 2. نموذج الإدخال --}}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 animate__animated animate__fadeInUp">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-dark"><i class="fas fa-id-card me-2 text-secondary"></i> بيانات المستخدم الأساسية</h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('system.users.store', $school->id) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">الاسم الكامل <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-start-0" required placeholder="مثال: أحمد محمد" value="{{ old('name') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-bold text-dark">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 text-start" dir="ltr" required placeholder="admin@school.com" value="{{ old('email') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">كلمة المرور <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-start-0" required placeholder="********" minlength="8">
                                </div>
                                <div class="form-text">يجب أن تتكون من 8 أحرف على الأقل.</div>
                            </div>
                            
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-bold text-dark">صلاحية المستخدم (الرتبة) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-shield text-muted"></i></span>
                                    <select name="role" class="form-select border-start-0" required>
                                        <option value="" disabled selected>-- اختر الصلاحية --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}">
                                                @if($role->name == 'admin') مدير مدرسة
                                                @elseif($role->name == 'teacher') معلم
                                                @else {{ $role->name }} 
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('system.schools.index') }}" class="btn btn-light px-4 fw-bold rounded-pill text-dark shadow-sm">إلغاء</a>
                            <button type="submit" class="btn btn-dark px-5 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-2 text-warning"></i> حفظ وإنشاء الحساب
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection