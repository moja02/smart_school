@extends('layouts.manager')

@section('content')

<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white" style="border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white"><i class="fas fa-user-shield text-warning me-2"></i> إدارة صلاحيات المشرفين</h2>
            <p class="mb-0 text-white-50">تفعيل أو تعطيل الأقسام والخصائص المتاحة لمساعديك في الإدارة.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-key fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success fw-bold shadow-sm rounded-pill px-4 py-3 mb-4 border-0">
        <i class="fas fa-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
    </div>
@endif

<div class="row">
    @forelse($admins as $admin)
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <div class="avatar-sm bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 45px; height: 45px;">
                        {{ mb_substr($admin->name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="m-0 fw-bold text-dark">{{ $admin->name }}</h5>
                        <small class="text-muted"><i class="fas fa-envelope me-1"></i> {{ $admin->email }}</small>
                    </div>
                </div>
                
                <div class="card-body bg-light">
                    <form action="{{ route('manager.admins.permissions.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="admin_id" value="{{ $admin->id }}">
                        
                        <div class="row g-3">
                            @foreach($availablePermissions as $key => $label)
                                @php 
                                    // التحقق مما إذا كان الأدمن يمتلك هذه الصلاحية حالياً
                                    $hasPermission = $admin->hasPermissionTo($key); 
                                @endphp
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-3 bg-white border rounded shadow-sm d-flex justify-content-between align-items-center h-100 hover-scale">
                                        <label class="form-check-label fw-bold text-dark mb-0 w-100 cursor-pointer" for="perm_{{ $admin->id }}_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="permissions[]" value="{{ $key }}" id="perm_{{ $admin->id }}_{{ $key }}" {{ $hasPermission ? 'checked' : '' }} style="width: 2.5rem; height: 1.2rem;">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> حفظ صلاحيات هذا المشرف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center fw-bold border-0 shadow-sm rounded-pill py-4">
                <i class="fas fa-info-circle me-2 fs-4 align-middle"></i> لم يتم إضافة أي مشرفين (Admins) في هذه المدرسة بعد.
            </div>
        </div>
    @endforelse
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .form-switch .form-check-input { margin-left: -2.5em; /* ضبط زر الـ Switch للعربية */ }
    .hover-scale { transition: all 0.2s ease; }
    .hover-scale:hover { transform: translateY(-2px); border-color: #3498db !important; }
</style>

@endsection