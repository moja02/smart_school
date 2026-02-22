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

        /* تنسيق الهيدر الملون */
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

        /* تنسيق القائمة الجانبية لتطابق الأدمن */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: #212529; /* نفس لون الأدمن الداكن */
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

        /* للتجاوب مع الشاشات الصغيرة */
        @media (max-width: 992px) {
            .sidebar { right: -260px; }
            .main-content { margin-right: 0; }
            .sidebar.active { right: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar shadow">
        <div class="text-center mb-4 fs-4 fw-bold text-white">Smart School 🎓</div>
        
        <a href="{{ route('manager.dashboard') }}" class="{{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> لوحة القيادة
        </a>
        
        <div class="text-uppercase small text-muted fw-bold px-3 mt-3 mb-1">الإدارة</div>

        <a href="{{ route('manager.create_admin') }}" class="{{ request()->routeIs('manager.create_admin') ? 'active' : '' }}">
            <i class="fas fa-user-shield text-warning"></i> تعيين مسؤول (Admin)
        </a>

        <div class="text-uppercase small text-muted fw-bold px-3 mt-3 mb-1">المتابعة</div>

        <a href="{{ route('manager.teachers.index') }}" class="{{ request()->routeIs('manager.teachers.index') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> سجل المعلمين
        </a>

        <form action="{{ route('logout') }}" method="POST" class="mt-5 px-3">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-sign-out-alt"></i> تسجيل خروج
            </button>
        </form>
    </div>

    <div class="main-content">
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-4 px-3">
            <div class="container-fluid">
                <span class="navbar-brand text-secondary">بوابة المدير العام</span>
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold">{{ Auth::user()->name }}</span>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
        </nav>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>
</html>