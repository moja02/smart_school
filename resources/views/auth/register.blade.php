<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب جديد | Smart School</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #121212;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            border: 1px solid #333;
        }
        .register-title {
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .register-subtitle {
            color: #aaaaaa;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        .form-control, .form-select {
            background-color: #2c2c2c;
            border: 1px solid #444;
            color: #ffffff;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            background-color: #333;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            color: #ffffff;
        }
        .form-label {
            color: #cccccc;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-register {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
        }
        .login-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .login-link:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h3 class="register-title">إنشاء حساب جديد</h3>
        <p class="register-subtitle">انضم إلى النظام المدرسي الذكي</p>

        @if ($errors->any())
            <div class="alert alert-danger p-3 mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="name" class="form-control" placeholder="أدخل اسمك الكامل" required value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" placeholder="example@school.com" required value="{{ old('email') }}">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">نوع الحساب (الدور)</label>
                <select name="role" class="form-select" required>
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>اختر نوع الحساب...</option>
                    <option value="student" @selected(old('role') === 'student')>👨‍🎓 طالب</option>
                    <option value="teacher" @selected(old('role') === 'teacher')>👨‍🏫 معلم</option>
                    <option value="parent"  @selected(old('role') === 'parent')>👨‍👩‍👦 ولي أمر</option>
                </select>
            </div>

            <button type="submit" class="btn btn-register w-100">
                إنشاء الحساب الآن
            </button>
        </form>

        <p class="mt-4 text-center text-muted" style="font-size: 0.9rem;">
            لديك حساب مسبقاً؟ <a href="{{ route('login.form') }}" class="login-link">سجل دخولك من هنا</a>
        </p>
    </div>

</body>
</html>
