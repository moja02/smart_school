@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">إدارة المستخدمين 👥</h3>
            <p class="mb-0 opacity-75">عرض والتحكم في حسابات النظام.</p>
        </div>
        <i class="fas fa-users fa-3x opacity-50"></i>
    </div>
</div>

{{-- ========================================== --}}
{{-- ✅ نموذج الفلترة والبحث --}}
{{-- ========================================== --}}
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-filter me-2"></i> بحث وتصفية</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users') }}" method="GET">
            <div class="row g-3 align-items-end">
                
                {{-- 1. البحث بالاسم أو الإيميل --}}
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">بحث عام</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               value="{{ request('search') }}" 
                               placeholder="اسم المستخدم أو البريد...">
                    </div>
                </div>

                {{-- 2. الفلترة حسب الصلاحية --}}
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-bold">نوع المستخدم</label>
                    <select name="role" class="form-select">
                        <option value="">-- الكل --</option>
                        <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>طالب</option>
                        <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                    </select>
                </div>

                {{-- 3. أزرار التحكم --}}
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter me-1"></i> تصفية
                        </button>
                        
                        @if(request()->has('search') || request()->has('role'))
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary shadow-sm" title="إلغاء الفلاتر">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- زر إضافة مستخدم جديد --}}
                <div class="col-md-2 text-end">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success w-100 shadow-sm">
                        <i class="fas fa-plus me-1"></i> جديد
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ========================================== --}}
{{-- جدول العرض --}}
{{-- ========================================== --}}
<div class="card shadow border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الصلاحية</th>
                        <th>تاريخ التسجيل</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    {{-- تلوين الصف بالأحمر إذا كان المستخدم محظوراً --}}
                    <tr class="{{ $user->is_banned ? 'table-danger opacity-75' : '' }}">
                        <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="fw-bold {{ $user->is_banned ? 'text-danger text-decoration-line-through' : '' }}">{{ $user->name }}</span>
                                    @if($user->is_banned)
                                        <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">محظور</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small">{{ $user->email }}</td>
                        <td class="text-center">
                            @switch($user->role)
                                @case('teacher')
                                    <span class="badge bg-success px-3 py-2 rounded-pill">معلم</span>
                                    @break

                                @case('student')
                                    <span class="badge bg-primary px-3 py-2 rounded-pill">طالب</span>
                                    @break

                                @case('parent')
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill">ولي أمر</span>
                                    @break

                                @default
                                    <span class="badge bg-light text-dark">{{ $user->role }}</span>
                            @endswitch
                        </td>
                        <td class="text-muted small">{{ $user->created_at->format('Y-m-d') }}</td>
                        
                        {{-- ========================================== --}}
                        {{-- منطقة الإجراءات --}}
                        {{-- ========================================== --}}
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                
                                {{-- زر الحظر وإلغاء الحظر --}}
                                <form action="{{ route('admin.users.toggleBan', $user->id) }}" method="POST" class="d-inline form-ban">
                                    @csrf
                                    @if($user->is_banned)
                                        <button type="button" class="btn btn-sm btn-success text-white btn-ban" data-action="unban" title="إلغاء الحظر وتفعيل الحساب">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-dark btn-ban" data-action="ban" title="حظر وتجميد الحساب">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </form>

                                {{-- زر التعديل --}}
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                {{-- زر تصفير كلمة المرور --}}
                                <form action="{{ route('admin.users.resetPassword', $user->id) }}" method="POST" class="d-inline form-reset">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark btn-reset" title="تصفير كلمة المرور">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>
                                
                                {{-- زر الحذف --}}
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="d-inline form-delete">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="حذف نهائي">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                        {{-- ========================================== --}}

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                            <p>لا توجد نتائج تطابق بحثك.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- الترقيم --}}
        <div class="p-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // ============================================
        // 1. كود تصفير كلمة المرور (أصفر)
        // ============================================
        const resetButtons = document.querySelectorAll('.btn-reset');
        resetButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('.form-reset');
                
                Swal.fire({
                    title: 'تصفير كلمة المرور؟',
                    text: "سيعود الباسورد إلى: 12345678",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، صفرها',
                    cancelButtonText: 'تراجع',
                    color: '#000'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // ============================================
        // 2. كود الحظر وإلغاء الحظر (أسود/أخضر - جديد)
        // ============================================
        const banButtons = document.querySelectorAll('.btn-ban');
        banButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('.form-ban');
                const action = this.getAttribute('data-action');
                
                const titleText = action === 'ban' ? 'حظر المستخدم؟' : 'إلغاء حظر المستخدم؟';
                const descText = action === 'ban' ? 'لن يتمكن هذا المستخدم من الدخول إلى حسابه في النظام.' : 'سيتم تفعيل حساب هذا المستخدم ليتمكن من الدخول مجدداً.';
                const confirmColor = action === 'ban' ? '#212529' : '#198754';
                const confirmBtnText = action === 'ban' ? 'نعم، قم بالحظر' : 'نعم، ألغِ الحظر';

                Swal.fire({
                    title: titleText,
                    text: descText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'تراجع'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // ============================================
        // 3. كود الحذف النهائي (أحمر)
        // ============================================
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('.form-delete');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "لا يمكن التراجع عن هذا الإجراء! سيتم حذف المستخدم وجميع بياناته.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذفه!',
                    cancelButtonText: 'تراجع'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

    });
</script>
@endsection