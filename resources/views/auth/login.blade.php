<!DOCTYPE html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><title>تسجيل الدخول</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light"><div class="container mt-5"><div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow"><div class="card-body">
<h3 class="text-center mb-4">تسجيل الدخول</h3>

@if ($errors->any())
<div class="alert alert-danger"><ul class="mb-0">
@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
</ul></div>
@endif

<form method="POST" action="{{ route('login') }}">
@csrf
<div class="mb-3"><label class="form-label">البريد الإلكتروني</label>
<input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>

<div class="mb-3"><label class="form-label">كلمة المرور</label>
<input type="password" name="password" class="form-control" required></div>

<button class="btn btn-primary w-100">دخول</button>
</form>

<p class="mt-3 text-center">مستخدم جديد؟ <a href="{{ route('register.form') }}">إنشاء حساب</a></p>
</div></div></div></div></div></body></html>
