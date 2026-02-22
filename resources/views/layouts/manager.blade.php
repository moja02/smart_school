<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة المدير - Smart School</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        
        <a href="{{ route('manager.dashboard') }}" class="{{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> لوحة القيادة
        </a>

        <a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> الملف الشخصي
        </a>
        
        <a href="{{ route('manager.create_admin') }}" class="{{ request()->routeIs('manager.create_admin') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> تعيين مسؤول (Admin)
        </a>

        <a href="{{ route('manager.teachers.index') }}" class="{{ request()->routeIs('manager.teachers.index') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> سجل المعلمين
        </a>

        <a href="{{ route('manager.system_logs') }}" class="sidebar-link {{ request()->routeIs('manager.system_logs') ? 'active' : '' }}">
            <i class="fas fa-history"></i> <span>سجل النظام (Tracking)</span>
        </a>

        <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-comments"></i> المحادثات
            </div>
            
            {{-- 👇 هذا هو كود الإشعار --}}
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    <div class="main-content">
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-4 px-3">
            <div class="container-fluid">
                <span class="navbar-brand text-secondary">بوابة المدير العام</span>
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold">{{ Auth::user()->name }}</span>
                    {{-- زر تسجيل الخروج نفس شكل صفحة الأدمن بالضبط --}}
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
</body>
</html>