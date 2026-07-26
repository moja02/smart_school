<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | Smart School</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f7f6;
            color: #333333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 2.5rem 2rem;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }
        .login-title {
            font-weight: 700;
            color: #333333;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .login-subtitle {
            color: #666666;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        .form-control {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            color: #333333;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            color: #333333;
        }
        .form-label {
            color: #555555;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-login {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
        }
        .register-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
            border-radius: 8px;
        }
        .alert-success {
            background-color: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h3 class="login-title">تسجيل الدخول</h3>
        <p class="login-subtitle">مرحباً بك مجدداً في نظام المدرسة الذكي</p>

        @if(session('success'))
            <div class="alert alert-success p-3 mb-4 text-center">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger p-3 mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" placeholder="example@school.com" required value="{{ old('email') }}">
            </div>

            <div class="mb-4">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label text-muted" for="remember" style="font-size: 0.9rem;">تذكرني</label>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-4">
                تسجيل الدخول
            </button>
        </form>

        <div class="text-center">
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                ليس لديك حساب؟ <a href="{{ route('register.form') }}" class="register-link">إنشاء حساب جديد</a>
            </p>
        </div>
    </div>

</body>
</html>
