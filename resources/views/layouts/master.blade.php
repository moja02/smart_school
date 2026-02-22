<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart School Dashboard</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f3f4f6; overflow-x: hidden; }
        
        /* Sidebar Styling */
        .sidebar {
            min-width: 260px;
            max-width: 260px;
            background: #2c3e50;
            color: #fff;
            min-height: 100vh;
            transition: all 0.3s;
        }
        .sidebar .brand {
            padding: 20px;
            font-size: 1.4rem;
            font-weight: bold;
            text-align: center;
            background: #1a252f;
            color: #ecf0f1;
            border-bottom: 1px solid #34495e;
        }
        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 1rem;
            color: #bdc3c7;
            display: block;
            transition: 0.3s;
            border-right: 4px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background: #34495e;
            border-right-color: #3498db;
        }
        .sidebar i { margin-left: 10px; width: 25px; text-align: center; }

        /* Content Area */
        .content { width: 100%; }
        
        /* Navbar */
        .top-navbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="brand"><i class="fas fa-school"></i> Smart School</div>
        
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> لوحة التحكم
        </a>
        
        @if(Auth::user()->role == 'admin')
        <a href="{{ route('admin.assign') }}" class="{{ request()->routeIs('admin.assign') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> توزيع المواد
        </a>
        <a href="{{ route('admin.students') }}" class="{{ request()->routeIs('admin.students') ? 'active' : '' }}">
            <i class="fas fa-user-graduate"></i> الطلاب
        </a>
        @endif

        @if(Auth::user()->role == 'teacher')
        <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
            <i class="fas fa-book-reader"></i> موادي وطلابي
        </a>
        @endif
    </div>

    <div class="content">
        <div class="top-navbar">
            <h5 class="m-0 text-secondary">مرحباً، {{ Auth::user()->name }} 👋</h5>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger btn-sm rounded-pill px-4">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </button>
            </form>
        </div>

        <div class="p-4">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>