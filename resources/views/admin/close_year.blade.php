@extends('layouts.admin')

@section('title', 'إغلاق السنة الدراسية')

@section('content')

{{-- الترويسة --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">إغلاق السنة الدراسية 🏫</h2>
            <p class="mb-0 opacity-75">
                هذه الصفحة تقوم بتنظيف جميع البيانات المؤقتة (الدرجات، التقييمات، الجدول، الحضور) وتجهيز النظام لسنة دراسية جديدة.
            </p>
            <div class="mt-3">
                <span class="badge bg-light text-dark shadow-sm px-3 py-2 fs-6">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> السنة الحالية: {{ $school->academic_year ?? 'غير محددة' }}
                </span>
            </div>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-door-closed fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

{{-- تحذير مهم --}}
<div class="alert alert-danger border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <i class="fas fa-exclamation-triangle me-3 fs-3 text-danger mt-1"></i>
        <div>
            <strong class="d-block mb-1">⚠️ تنبيه هام جداً</strong>
            <span>هذه العملية <strong>لا يمكن التراجع عنها</strong>. ستقوم بحذف جميع الدرجات والتقييمات والجدول الدراسي والحضور والغياب وإسناد المواد للسنة الحالية. تأكد أنك قمت بترحيل <strong>جميع الصفوف</strong> قبل المتابعة.</span>
        </div>
    </div>
</div>

{{-- إحصائيات ما سيتم تنظيفه --}}
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i> ملخص البيانات التي سيتم تنظيفها</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4 col-6">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-users fs-3 text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $stats['students_count'] }}</h4>
                    <small class="text-muted">طالب مسجل</small>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-star-half-alt fs-3 text-warning mb-2"></i>
                    <h4 class="mb-0">{{ $stats['scores_count'] }}</h4>
                    <small class="text-muted">سجل درجات</small>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-clipboard-check fs-3 text-success mb-2"></i>
                    <h4 class="mb-0">{{ $stats['assessments_count'] }}</h4>
                    <small class="text-muted">تقييم</small>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-table fs-3 text-info mb-2"></i>
                    <h4 class="mb-0">{{ $stats['schedules_count'] }}</h4>
                    <small class="text-muted">حصة في الجدول</small>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-calendar-check fs-3 text-secondary mb-2"></i>
                    <h4 class="mb-0">{{ $stats['attendance_count'] }}</h4>
                    <small class="text-muted">سجل حضور/غياب</small>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded p-3 text-center {{ $stats['promotions_done'] > 0 ? '' : 'bg-danger bg-opacity-10' }}">
                    <i class="fas fa-exchange-alt fs-3 {{ $stats['promotions_done'] > 0 ? 'text-success' : 'text-danger' }} mb-2"></i>
                    <h4 class="mb-0">{{ $stats['promotions_done'] }}</h4>
                    <small class="text-muted">عملية ترحيل مكتملة</small>
                </div>
            </div>
        </div>

        @if($stats['promotions_done'] == 0)
            <div class="alert alert-warning mt-3 mb-0 border-0">
                <i class="fas fa-info-circle me-2"></i>
                <strong>تنبيه:</strong> لم يتم تنفيذ أي عملية ترحيل بعد! يجب ترحيل جميع الصفوف أولاً من 
                <a href="{{ route('admin.promotion.index') }}" class="alert-link">صفحة الترحيل</a>.
            </div>
        @endif
    </div>
</div>

{{-- نموذج الإغلاق --}}
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="m-0 fw-bold text-danger"><i class="fas fa-door-closed me-2"></i> تنفيذ إغلاق السنة</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-bold text-dark">السنة الدراسية الجديدة</label>
                <input type="text" id="newAcademicYear" class="form-control" placeholder="مثال: 2027-2028" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-dark">اكتب "<span class="text-danger">تأكيد</span>" للمتابعة</label>
                <input type="text" id="confirmText" class="form-control" placeholder="اكتب: تأكيد">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="btnCloseYear" class="btn btn-danger w-100" disabled>
                    <i class="fas fa-lock me-2"></i> إغلاق السنة وتنظيف البيانات
                </button>
            </div>
        </div>

        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                سيتم حذف: الدرجات، التقييمات، الجدول الدراسي، الحضور والغياب، إسناد المواد، ومحاولات الاختبارات الذاتية.
                <br>
                <i class="fas fa-shield-alt me-1"></i>
                <strong>لن يتم حذف:</strong> حسابات المستخدمين، الفصول، المواد، أرشيف الترحيل، أو الدروس وبنك الأسئلة.
            </small>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // تفعيل الزر فقط عند كتابة "تأكيد"
    document.getElementById('confirmText').addEventListener('input', function() {
        let btn = document.getElementById('btnCloseYear');
        if (this.value.trim() === 'تأكيد') {
            btn.disabled = false;
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-danger');
        } else {
            btn.disabled = true;
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-secondary');
        }
    });

    document.getElementById('btnCloseYear').addEventListener('click', function() {
        let newYear = document.getElementById('newAcademicYear').value.trim();
        let confirmText = document.getElementById('confirmText').value.trim();

        if (!newYear) {
            Swal.fire('خطأ', 'يجب تحديد السنة الدراسية الجديدة!', 'error');
            return;
        }
        if (confirmText !== 'تأكيد') {
            Swal.fire('خطأ', 'يجب كتابة كلمة "تأكيد" للمتابعة!', 'error');
            return;
        }

        Swal.fire({
            title: '⚠️ تحذير نهائي!',
            html: `
                <p class="mb-2">أنت على وشك إغلاق السنة الدراسية الحالية <strong>({{ $school->academic_year ?? '' }})</strong></p>
                <p class="mb-2">وفتح سنة جديدة: <strong>${newYear}</strong></p>
                <p class="text-danger fw-bold">سيتم حذف جميع الدرجات والتقييمات والجداول والحضور نهائياً!</p>
                <p>هل أنت متأكد تماماً؟</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، أغلق السنة!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'جاري الإغلاق...',
                    text: 'يرجى الانتظار وعدم إغلاق الصفحة. هذه العملية قد تستغرق بعض الوقت.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('{{ route("admin.close_year.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        new_academic_year: newYear,
                        confirm_text: confirmText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'تم بنجاح! 🎓',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('خطأ', 'فشل في الاتصال بالسيرفر.', 'error');
                });
            }
        });
    });
</script>
@endsection
