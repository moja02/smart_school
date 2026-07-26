<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مرحباً بك في Smart School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: "Tahoma", Arial, sans-serif; background-color: #f8f9fa; }
        .hero-section {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 100px 0;
            text-align: center;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }
        .hero-section h1 { font-weight: bold; font-size: 3.5rem; margin-bottom: 20px; }
        .hero-section p { font-size: 1.2rem; margin-bottom: 40px; opacity: 0.9; }
        .feature-icon { font-size: 3rem; color: #3498db; margin-bottom: 15px; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="#">Smart School <i class="fas fa-graduation-cap"></i></a>
            <div class="d-flex gap-3">
                <a href="{{ route('admissions.create') }}" class="btn btn-outline-primary fw-bold">تقديم طلب تسجيل</a>
                <a href="{{ route('login.form') }}" class="btn btn-primary fw-bold">تسجيل الدخول</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section shadow-lg">
        <div class="container">
            <h1>مستقبل أبنائك يبدأ من هنا</h1>
            <p>نظام مدرسي متكامل يربط بين الإدارة، المعلمين، وأولياء الأمور لضمان أفضل تجربة تعليمية.</p>
            <a href="{{ route('admissions.create') }}" class="btn btn-light btn-lg text-primary fw-bold px-5 rounded-pill shadow">سجل ابنك الآن <i class="fas fa-arrow-left ms-2"></i></a>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5 mt-5">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-4">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h4 class="fw-bold">متابعة مستمرة</h4>
                    <p class="text-muted">تابع مستوى أبنائك، الحضور، والواجبات المدرسية لحظة بلحظة عبر بوابة ولي الأمر.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-4">
                    <i class="fas fa-wallet feature-icon"></i>
                    <h4 class="fw-bold">المحفظة الرقمية</h4>
                    <p class="text-muted">ادفع الرسوم المدرسية واشحن مصروف المقصف بكل سهولة وبدون تعاملات نقدية.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 p-4">
                    <i class="fas fa-comments feature-icon"></i>
                    <h4 class="fw-bold">تواصل مباشر</h4>
                    <p class="text-muted">تواصل بشكل مباشر مع المعلمين والإدارة المدرسية من خلال نظام المراسلات المدمج.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} Smart School. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

</body>
</html>
