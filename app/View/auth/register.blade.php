<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إنشاء حساب جديد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-body">
          <h3 class="text-center mb-4">إنشاء حساب جديد</h3>

          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label">الاسم الكامل</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">البريد الإلكتروني</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">كلمة المرور</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            

            <div class="mb-3">
              <label for="role" class="form-label">الدور</label>
              <select name="role" class="form-select" required>
                <option value="">اختر الدور</option>
                <option value="student">طالب</option>
                <option value="teacher">معلم</option>
                <option value="parent">ولي أمر</option>
              </select>
            </div>

            <button type="submit" class="btn btn-success w-100">إنشاء الحساب</button>
          </form>

          <p class="mt-3 text-center">
            لديك حساب؟ <a href="{{ route('login.form') }}">تسجيل الدخول</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
