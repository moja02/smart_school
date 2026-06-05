@extends('layouts.system')
@section('content')

{{-- 1. ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow border-0" style="background: linear-gradient(135deg, #2d3436, #000000);">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-white">🌐 لوحة تحكم النظام (Super Admin)</h2>
            <p class="mb-0 text-white opacity-75">إدارة المدارس المشتركة في النظام، وإنشاء حسابات الإدارة لكل مدرسة.</p>
        </div>
        <div class="text-end d-flex gap-2">
            {{-- زر إضافة مدرسة يفتح نافذة منبثقة (Modal) --}}
            <button type="button" class="btn btn-outline-light rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addSchoolModal">
                <i class="fas fa-plus-circle me-2 text-warning"></i> تسجيل مدرسة جديدة
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif

{{-- 2. عرض المدارس بنظام البطاقات --}}
@if($schools->count() > 0)
    <div class="row">
        @foreach($schools as $school)
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card shadow border-0 h-100 animate__animated animate__fadeIn">
                
                {{-- رأس البطاقة --}}
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-school me-2 text-secondary opacity-50"></i> {{ $school->name }}
                    </h5>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2">
                        معرف: {{ $school->id }}
                    </span>
                </div>

                {{-- جسم البطاقة: معلومات المدرسة --}}
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-map-marker-alt text-muted me-3 w-15px text-center"></i>
                            <span class="text-dark">{{ $school->address ?? 'العنوان غير مسجل' }}</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-phone-alt text-muted me-3 w-15px text-center"></i>
                            <span class="text-dark" dir="ltr">{{ $school->phone ?? 'رقم الهاتف غير مسجل' }}</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="fas fa-users text-muted me-3 w-15px text-center"></i>
                            <span class="text-dark">إجمالي المستخدمين: <strong class="text-primary">{{ $school->users_count }}</strong></span>
                        </li>
                    </ul>
                </div>
                
                {{-- تذييل البطاقة: الإجراءات --}}
                <div class="card-footer bg-light border-top-0 py-3 text-center">
                    <a href="{{ route('system.users.create', $school->id) }}" class="btn btn-dark w-100 rounded-pill fw-bold">
                        <i class="fas fa-user-plus me-2 text-warning"></i> إنشاء حساب لهذه المدرسة
                    </a>
                </div>

            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="card shadow border-0 py-5">
        <div class="card-body text-center py-5">
            <div class="mb-4 opacity-25">
                <i class="fas fa-globe fa-5x text-muted"></i>
            </div>
            <h4 class="fw-bold text-secondary">لا توجد مدارس مسجلة في النظام بعد</h4>
            <button type="button" class="btn btn-dark btn-lg px-5 rounded-pill shadow mt-3" data-bs-toggle="modal" data-bs-target="#addSchoolModal">
                <i class="fas fa-plus-circle me-2 text-warning"></i> تسجيل أول مدرسة
            </button>
        </div>
    </div>
@endif

{{-- 3. نافذة منبثقة (Modal) لإضافة مدرسة جديدة --}}
<div class="modal fade" id="addSchoolModal" tabindex="-1" aria-labelledby="addSchoolModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="addSchoolModalLabel"><i class="fas fa-plus-circle me-2 text-primary"></i> تسجيل مدرسة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('system.schools.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">اسم المدرسة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: مدرسة النور الأهلية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">العنوان</label>
                        <input type="text" name="address" class="form-control" placeholder="المدينة، الشارع...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control" placeholder="09X XXX XXXX">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="fas fa-save me-2 text-warning"></i> حفظ المدرسة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection