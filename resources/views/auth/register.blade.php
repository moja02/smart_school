<!DOCTYPE html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><title>إنشاء حساب</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light"><div class="container mt-5"><div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow"><div class="card-body">
<h3 class="text-center mb-4">إنشاء حساب</h3>

@if ($errors->any())
<div class="alert alert-danger"><ul class="mb-0">
@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
</ul></div>
@endif

<form method="POST" action="{{ route('register') }}">
@csrf
<div class="mb-3"><label class="form-label">الاسم الكامل</label>
<input type="text" name="name" class="form-control" required value="{{ old('name') }}"></div>

<div class="mb-3"><label class="form-label">البريد الإلكتروني</label>
<input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>

<div class="mb-3"><label class="form-label">كلمة المرور</label>
<input type="password" name="password" class="form-control" required></div>

<div class="mb-3"><label class="form-label">تأكيد كلمة المرور</label>
<input type="password" name="password_confirmation" class="form-control" required></div>

<div class="mb-3"><label class="form-label">الدور</label>
<select name="role" class="form-select" required>
<option value="">اختر الدور</option>
<option value="student" @selected(old('role')==='student')>طالب</option>
<option value="teacher" @selected(old('role')==='teacher')>معلم</option>
<option value="parent"  @selected(old('role')==='parent')>ولي أمر</option>
</select></div>

<button class="btn btn-success w-100">إنشاء الحساب</button>
</form>

<p class="mt-3 text-center">لديك حساب؟ <a href="{{ route('login.form') }}">تسجيل الدخول</a></p>
</div></div></div></div></div></body></html>
