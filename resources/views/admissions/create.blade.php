<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بوابة القبول والتسجيل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: "Tahoma", Arial, sans-serif; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="{{ url('/') }}">Smart School <i class="fas fa-graduation-cap"></i></a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                @if(session('success'))
                    <div class="alert alert-success text-center fs-5 shadow-sm rounded-4 py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i><br>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-primary text-white text-center py-4 rounded-top-4">
                        <h3 class="mb-0">طلب تسجيل طالب جديد</h3>
                        <p class="mb-0 opacity-75 mt-1">الرجاء تعبئة بيانات ولي الأمر والطالب بدقة</p>
                    </div>
                    <div class="card-body p-5">
                        <form action="{{ route('admissions.store') }}" method="POST">
                            @csrf
                            
                            <h5 class="text-secondary mb-3 border-bottom pb-2"><i class="fas fa-user-tie me-2"></i> بيانات ولي الأمر</h5>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">الاسم الرباعي</label>
                                    <input type="text" name="parent_name" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">رقم الهاتف</label>
                                    <input type="text" name="parent_phone" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">البريد الإلكتروني</label>
                                    <input type="email" name="parent_email" class="form-control form-control-lg" required>
                                </div>
                            </div>

                            <h5 class="text-secondary mb-3 border-bottom pb-2"><i class="fas fa-child me-2"></i> بيانات الطالب</h5>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">الاسم الرباعي للطالب</label>
                                    <input type="text" name="student_name" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">تاريخ الميلاد</label>
                                    <input type="date" name="student_dob" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">الصف المطلوب التسجيل به</label>
                                    <select name="grade_id" class="form-select form-select-lg" required>
                                        <option value="">-- اختر المرحلة --</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">ملاحظات إضافية (اختياري)</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow w-100 fw-bold">
                                    إرسال طلب التسجيل <i class="fas fa-paper-plane ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
