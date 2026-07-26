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
<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
    <div class="card-body p-4">
        <form action="{{ route('admin.users') }}" method="GET" id="searchForm">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="form-floating">
                        <input type="text" name="search" class="form-control" id="searchUser" placeholder="الاسم أو البريد..." value="{{ request('search') }}" style="border-radius: 10px;">
                        <label for="searchUser"><i class="fas fa-search text-muted me-1"></i> ابحث عن مستخدم...</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-floating">
                        <select name="role" class="form-select select2-ui" id="roleSelect" style="border-radius: 10px;">
                            <option value="">جميع الصلاحيات</option>
                            <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>طالب</option>
                            <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                        </select>
                        <label for="roleSelect"><i class="fas fa-user-shield text-muted me-1"></i> نوع المستخدم</label>
                    </div>
                </div>

                <div class="col-md-4 d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary h-100 flex-grow-1 shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-filter me-1"></i> عرض النتائج
                    </button>
                    
                    @if(request()->has('search') || request()->has('role'))
                        <a href="{{ route('admin.users') }}" class="btn btn-light h-100 shadow-sm border" title="مسح الفلاتر" style="border-radius: 10px;">
                            <i class="fas fa-undo text-danger"></i>
                        </a>
                    @endif

                    <a href="{{ route('admin.users.create') }}" class="btn btn-success h-100 shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-plus me-1"></i> إضافة
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ========================================== --}}
{{-- جدول العرض --}}
{{-- ========================================== --}}
<div class="card shadow border-0" id="usersTableContainer">
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
        // ============================================
        // 🛡️ تفعيل رسائل التنبيه (SweetAlert)
        // ============================================
        function bindSweetAlerts() {
            // 1. كود تصفير كلمة المرور (أصفر)
            const resetButtons = document.querySelectorAll('.btn-reset');
            resetButtons.forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', function (e) {
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

            // 2. كود الحظر وإلغاء الحظر (أسود/أخضر - جديد)
            const banButtons = document.querySelectorAll('.btn-ban');
            banButtons.forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', function (e) {
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
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                // Remove old event listeners to prevent duplicates if bound multiple times
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                
                newButton.addEventListener('click', function (e) {
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
        }
        
        // Initial bind
        bindSweetAlerts();

        // ============================================
        // 🚀 البحث الحي (Live Search AJAX)
        // ============================================
        let searchTimeout = null;
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchUser');
        const roleSelect = document.getElementById('roleSelect');
        const tableContainer = document.getElementById('usersTableContainer');

        function fetchResults() {
            tableContainer.style.opacity = '0.5'; // تأثير بصري للتحميل
            
            const url = new URL(searchForm.action);
            if (searchInput.value.trim() !== '') {
                url.searchParams.set('search', searchInput.value);
            }
            if (roleSelect.value !== '') {
                url.searchParams.set('role', roleSelect.value);
            }

            // تحديث رابط الصفحة في المتصفح للحفاظ على الفلاتر عند التحديث
            window.history.pushState({}, '', url);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.getElementById('usersTableContainer');
                if (newTable) {
                    tableContainer.innerHTML = newTable.innerHTML;
                    tableContainer.style.opacity = '1';
                    bindSweetAlerts(); // إعادة تفعيل الأزرار بعد تحديث الجدول
                }
            })
            .catch(err => {
                console.error('Error fetching data:', err);
                tableContainer.style.opacity = '1';
            });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchResults, 400); // الانتظار 400 ملي ثانية بعد آخر ضغطة
        });

        roleSelect.addEventListener('change', fetchResults);
    });
</script>
@endsection