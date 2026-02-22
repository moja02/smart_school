@extends($layout)

@section('content')
<div class="container py-4">

    {{-- 1. الترويسة الرئيسية (بنفس تصميم الداشبورد: داكن وفخم) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-white">الملف الشخصي ⚙️</h2>
                <p class="mb-0 opacity-75">
                    أهلاً بك، <strong>{{ explode(' ', trim($user->name))[0] }}</strong>. يمكنك تحديث بياناتك الشخصية وإعدادات الأمان من هنا.
                </p>
            </div>
            
            {{-- الأيقونة الخلفية الشفافة --}}
            <div class="d-none d-md-block">
                <i class="fas fa-user-cog fa-4x opacity-25 text-white"></i>
            </div>
        </div>
    </div>

    {{-- 2. بطاقة الفورم (نفس ستايل البطاقات السفلية في الداشبورد) --}}
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-id-badge me-2"></i> البيانات الشخصية</h6>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- القسم الأول: المعلومات الأساسية --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-0 bg-light @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- حقل الإيميل (مغلق للقراءة فقط) --}}
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-bold text-muted">البريد الإلكتروني (لا يمكن تغييره)</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-secondary text-white border-0"><i class="fas fa-lock"></i></span>
                                    <input type="email" class="form-control border-0 bg-secondary text-white opacity-50" value="{{ $user->email }}" readonly disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">رقم الهاتف</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control border-0 bg-light text-start @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" dir="ltr" placeholder="+218...">
                                </div>
                                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-bold">تاريخ الميلاد</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="date" name="birth_date" class="form-control border-0 bg-light @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $user->birth_date) }}">
                                </div>
                                @error('birth_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold">العنوان السكني</label>
                            <textarea name="address" class="form-control border-0 bg-light shadow-sm @error('address') is-invalid @enderror" rows="2" placeholder="أدخل تفاصيل العنوان...">{{ old('address', $user->address) }}</textarea>
                            @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- القسم الثاني: الأمان --}}
                        <div class="card-header bg-white py-3 border-bottom-0 px-0 mt-5">
                            <h6 class="m-0 fw-bold text-warning"><i class="fas fa-key me-2"></i> الأمان وكلمة المرور</h6>
                        </div>
                        <div class="alert alert-warning border-0 shadow-sm small mb-4">
                            <i class="fas fa-info-circle me-1"></i> اترك الحقول أدناه فارغة إذا لم تكن ترغب في تغيير كلمة المرور الحالية.
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-0 bg-light @error('password') is-invalid @enderror" placeholder="••••••••">
                                </div>
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control border-0 bg-light" placeholder="••••••••">
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 opacity-25">

                        <div class="d-flex justify-content-end">
                            {{-- زر الحفظ مع تأثير hover-scale اللي طلبته --}}
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow hover-scale">
                                <i class="fas fa-save me-2"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ستايل الانيميشن الخاص بك --}}
<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); }
</style>

{{-- تنبيه SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'عملية ناجحة',
                text: "{{ session('success') }}",
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'حسناً',
                timer: 3000
            });
        @endif
    });
</script>
@endsection