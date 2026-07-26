<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>لوحة الأدمن - Smart School</title>
    
    @vite(['resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        
        body {
            background-color: #f4f6f9;
            font-family: "Tahoma", Arial, sans-serif;
        }

        .page-header-card {
            background: linear-gradient(135deg, #3a6073, #16222a);
            border-radius: 1rem;
            color: white;
            padding: 2rem;
            border: none;
        }

        .navbar-brand {
            font-weight: bold;
        }

        
        .sidebar {
            width: 260px;
            height: 100vh;
            background: #212529;
            color: #fff;
            position: fixed;
            top: 0; right: 0;
            padding-top: 20px;
            transition: 0.3s;
            z-index: 1000;
            overflow-y: auto; /* للسماح بالتمرير إذا زادت الروابط */
        }
        .main-content { margin-right: 260px; padding: 30px; }
        
        .sidebar a {
            display: flex; align-items: center; padding: 15px 25px; 
            color: #adb5bd; text-decoration: none;
            border-right: 4px solid transparent; transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #343a40; color: #fff; border-right-color: #3a6073;
        }
        .sidebar i { width: 25px; margin-left: 10px; text-align: center; }
        
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
        
        /* تخصيص السكرول بار للقائمة الجانبية */
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-track { background: #212529; }
        .sidebar::-webkit-scrollbar-thumb { background: #6c757d; border-radius: 10px; }

        /* Premium Dark Mode Styles */
        [data-bs-theme="dark"] body {
            background-color: #0f172a; 
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .sidebar {
            background-color: #0b1121;
            border-left: 1px solid #1e293b;
        }
        [data-bs-theme="dark"] .sidebar a:hover, [data-bs-theme="dark"] .sidebar a.active {
            background-color: #1e293b;
            color: #38bdf8;
            border-right-color: #38bdf8;
        }
        [data-bs-theme="dark"] .card, [data-bs-theme="dark"] .card-body {
            background-color: #1e293b !important;
            border: 1px solid #334155;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5), 0 2px 4px -1px rgba(0, 0, 0, 0.3);
        }
        [data-bs-theme="dark"] .card-header {
            background-color: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .page-header-card {
            background: linear-gradient(135deg, #1e293b, #0f172a) !important;
            border: 1px solid #334155;
        }
        [data-bs-theme="dark"] .table {
            color: #e2e8f0;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .table-light, [data-bs-theme="dark"] .table thead th {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border-bottom-color: #475569 !important;
        }
        [data-bs-theme="dark"] .table-hover tbody tr:hover {
            color: #f8fafc;
            background-color: #334155;
        }
        [data-bs-theme="dark"] .bg-white {
            background-color: #1e293b !important;
        }
        [data-bs-theme="dark"] .bg-light {
            background-color: #334155 !important;
        }
        [data-bs-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .text-gray-800 {
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .modal-content {
            background-color: #1e293b;
            border: 1px solid #334155;
        }
        [data-bs-theme="dark"] input.form-control, [data-bs-theme="dark"] select.form-select, [data-bs-theme="dark"] textarea.form-control, [data-bs-theme="dark"] .form-control.bg-light {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] input.form-control:focus, [data-bs-theme="dark"] select.form-select:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }
        [data-bs-theme="dark"] .navbar {
            background-color: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
        }
        [data-bs-theme="dark"] .text-primary {
            color: #38bdf8 !important;
        }

        /* Select2 Dark Mode Fixes */
        [data-bs-theme="dark"] .select2-container--default .select2-selection--single,
        [data-bs-theme="dark"] .select2-dropdown {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #38bdf8 !important;
            color: #fff !important;
        }
        [data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }
    </style>
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'تم بنجاح!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'حسناً'
            });
        });
    </script>
    @endif
</head>
<body>

    <div class="sidebar shadow">
        <div class="text-center mb-4 fs-4 fw-bold text-white">Smart School 🎓</div>
        
        {{-- ========================================== --}}
        {{-- زر التحكم في إغلاق/فتح الرصد (حسب الصلاحية) --}}
        {{-- ========================================== --}}
        @php
            $currentSchool = \App\Models\School::find(auth()->user()->school_id);
            $user = auth()->user();
        @endphp
        
        @if($currentSchool && ($user->role === 'manager' || $user->hasPermissionTo('toggle_grading')))
        <div class="px-3 my-3">
            <form action="{{ route('admin.grading.toggle', 1) }}" method="POST" class="mb-2">
                @csrf
                @if($currentSchool->grading_locked_sem1)
                    <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2" style="font-size: 0.85rem;">
                        <i class="fas fa-lock"></i> رصد ف1 مغلق (فتح)
                    </button>
                @else
                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2" style="font-size: 0.85rem;">
                        <i class="fas fa-unlock"></i> رصد ف1 متاح (إغلاق)
                    </button>
                @endif
            </form>
            <form action="{{ route('admin.grading.toggle', 2) }}" method="POST">
                @csrf
                @if($currentSchool->grading_locked_sem2)
                    <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2" style="font-size: 0.85rem;">
                        <i class="fas fa-lock"></i> رصد ف2 مغلق (فتح)
                    </button>
                @else
                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2" style="font-size: 0.85rem;">
                        <i class="fas fa-unlock"></i> رصد ف2 متاح (إغلاق)
                    </button>
                @endif
            </form>
        </div>
        <hr class="border-secondary mx-3 opacity-25">
        @endif
        {{-- ========================================== --}}


        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> الرئيسية
        </a>

        <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.index') ? 'active' : '' }}">
            <i class="fa-solid fa-comments"></i> المراسلات اللحظية
        </a>

        <a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
            الملف الشخصي
        </a>
        
        {{-- صلاحية إدارة المستخدمين --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_users'))
        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="fas fa-users-cog"></i> إدارة المستخدمين
        </a>

        <a href="{{ route('admin.parents.link') }}" class="{{ request()->routeIs('admin.parents*') ? 'active' : '' }}">
            <i class="fas fa-user-friends"></i> أولياء الأمور والطلاب
        </a>
        @endif

        {{-- صلاحية إدارة القبول والتسجيل --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_admissions'))
        <a href="{{ route('admin.admissions.index') }}" class="{{ request()->routeIs('admin.admissions*') ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i> طلبات القبول والتسجيل
        </a>
        @endif

        {{-- صلاحية إدارة الرسوم --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_fees'))
        <a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> إدارة الرسوم
        </a>
        @endif

        {{-- صلاحية إدارة الاستئذان --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_leaves'))
        <a href="{{ route('admin.leaves') }}" class="{{ request()->routeIs('admin.leaves*') ? 'active' : '' }}">
            <i class="fas fa-envelope-open-text"></i> طلبات الاستئذان
        </a>
        @endif

        {{-- صلاحية إدارة المحفظة والمقصف --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_wallet_charge') || $user->hasPermissionTo('manage_wallet_sell'))
        <a href="{{ route('admin.wallet') }}" class="{{ request()->routeIs('admin.wallet*') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i> المحفظة والمقصف
        </a>
        @endif

        {{-- صلاحية المحاسبة (مرتبطة بالرسوم) --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_fees'))
        <a href="{{ route('admin.accounting.expenses') }}" class="{{ request()->routeIs('admin.accounting.expenses*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> إدارة المصروفات
        </a>
        <a href="{{ route('admin.accounting.report') }}" class="{{ request()->routeIs('admin.accounting.report*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> التقرير المالي الشامل
        </a>
        @endif

        {{-- صلاحية إدارة المواد --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_subjects'))
        <a href="{{ route('admin.subjects') }}" class="{{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
            <i class="fas fa-book"></i> المواد الدراسية
        </a>

        <a href="{{ route('admin.subjects.grade_settings') }}" class="{{ request()->routeIs('admin.subjects.grade_settings') ? 'active' : '' }} ps-4">
            <i class="fas fa-percentage"></i> توزيع الدرجات
        </a>
        @endif

        {{-- صلاحية إدارة الفصول --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_classes'))
        <a href="{{ route('admin.classes') }}" class="{{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i> الفصول الدراسية
        </a>
        @endif

        {{-- صلاحية إسناد المعلمين وتوزيع المواد --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('assign_teachers'))
        <a href="{{ route('admin.assign') }}" class="{{ request()->routeIs('admin.assign') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> توزيع المواد
        </a>
        @endif

        {{-- صلاحية تعديل درجات الطلاب --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('edit_marks'))
        <a href="{{ route('admin.marks.edit') }}" class="{{ request()->routeIs('admin.marks.edit') ? 'active' : '' }}">
            <i class="fas fa-edit"></i> تعديل التقييمات
        </a>
        @endif

        {{-- صلاحية إدارة الجداول --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_schedules'))
        <a href="{{ route('admin.schedule.index') }}" class="{{ request()->routeIs('admin.schedule*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> إعداد الجداول الدراسية
        </a>
        @endif

        {{-- صلاحية التقارير --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_reports'))
        <a href="{{ route('admin.reports.advanced') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> التقارير والإحصائيات
        </a>
        @endif
        
        {{-- صلاحية الترحيل --}}
        @if($user->role === 'manager' || $user->hasPermissionTo('manage_promotion'))
        <a href="{{ route('admin.promotion.index') }}" class="{{ request()->routeIs('admin.promotion*') ? 'active' : '' }}">
            <i class="fas fa-graduation-cap"></i> الترحيل ونهاية السنة
        </a>
        <a href="{{ route('admin.close_year.index') }}" class="{{ request()->routeIs('admin.close_year*') ? 'active' : '' }} ps-4">
            <i class="fas fa-door-closed"></i> إغلاق السنة الدراسية
        </a>
        @endif

        {{-- المحادثات (متاحة للجميع) --}}
        <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-comments"></i> المحادثات
            </div>
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
            @endif
        </a>
        
        {{-- قسم خاص بمدير المدرسة فقط (Manager) للتحكم في المشرفين --}}
        @if($user->role === 'manager')
        <hr class="border-secondary mx-3 opacity-25">
        <a href="{{ route('manager.admins.permissions') }}" class="{{ request()->routeIs('manager.admins.permissions') ? 'active' : '' }} text-warning">
            <i class="fas fa-user-shield"></i> صلاحيات المشرفين
        </a>
        @endif

    </div>

    <div class="main-content">
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-4 px-3 align-items-center">
            <div class="container-fluid">
                <span class="navbar-brand text-secondary">لوحة التحكم</span>
                
                <!-- Search Bar -->
                <div class="mx-auto position-relative d-none d-md-block" style="max-width: 400px; width: 100%;">
                    <div class="input-group">
                        <input type="text" class="form-control rounded-pill border-secondary bg-light" placeholder="بحث عن طالب، معلم، ولي أمر..." id="globalSearchInput">
                        <button class="btn btn-outline-secondary rounded-pill ms-2" type="button"><i class="fas fa-search"></i></button>
                    </div>
                    <ul class="list-group position-absolute w-100 shadow d-none" id="globalSearchResults" style="z-index: 1050; max-height: 300px; overflow-y: auto; margin-top: 5px; border-radius: 10px;"></ul>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" id="darkModeToggle" title="الوضع الليلي">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span class="fw-bold d-none d-lg-inline">{{ Auth::user()->name }}</span>
                    <span class="badge {{ Auth::user()->role === 'manager' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ Auth::user()->role === 'manager' ? 'مدير المدرسة' : 'مشرف إداري' }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="fas fa-sign-out-alt"></i></button>
                    </form>
                </div>
            </div>
        </nav>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')

    <!-- Dark Mode & Global Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Dark Mode Logic ---
            const themeToggle = document.getElementById('darkModeToggle');
            const htmlElement = document.documentElement;
            const currentTheme = localStorage.getItem('theme') || 'light';
            
            if (currentTheme === 'dark') {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                themeToggle.innerHTML = '<i class="fas fa-sun text-warning"></i>';
            }

            themeToggle.addEventListener('click', () => {
                if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
                    htmlElement.setAttribute('data-bs-theme', 'light');
                    localStorage.setItem('theme', 'light');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                } else {
                    htmlElement.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun text-warning"></i>';
                }
            });

            // --- Global Search Logic ---
            const searchInput = document.getElementById('globalSearchInput');
            const searchResults = document.getElementById('globalSearchResults');

            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    if (query.length < 2) {
                        searchResults.classList.add('d-none');
                        return;
                    }

                    fetch(`/users/search-global?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (data.length > 0) {
                                searchResults.classList.remove('d-none');
                                data.forEach(item => {
                                    const li = document.createElement('li');
                                    li.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                                    
                                    let badgeClass = 'bg-secondary';
                                    let roleName = 'مستخدم';
                                    if(item.role === 'student') { badgeClass = 'bg-primary'; roleName = 'طالب'; }
                                    else if(item.role === 'teacher') { badgeClass = 'bg-success'; roleName = 'معلم'; }
                                    else if(item.role === 'parent') { badgeClass = 'bg-info text-dark'; roleName = 'ولي أمر'; }
                                    else if(item.role === 'manager') { badgeClass = 'bg-warning text-dark'; roleName = 'مدير مدرسة'; }
                                    else if(item.role === 'admin') { badgeClass = 'bg-dark'; roleName = 'مشرف'; }

                                    li.innerHTML = `<span><i class="fas fa-user-circle me-2 text-muted"></i> ${item.name}</span> <span class="badge ${badgeClass} rounded-pill">${roleName}</span>`;
                                    
                                    li.style.cursor = 'pointer';
                                    li.addEventListener('click', () => {
                                        window.location.href = `/messages/${item.id}`;
                                    });

                                    searchResults.appendChild(li);
                                });
                            } else {
                                searchResults.classList.add('d-none');
                            }
                        });
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.classList.add('d-none');
                    }
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>