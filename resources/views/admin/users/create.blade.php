@extends('layouts.admin')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card page-header-card mb-4 text-center shadow">
                <h3 class="fw-bold m-0">تسجيل مستخدم جديد 👤</h3>
                <p class="mb-0 opacity-75 mt-2">إضافة مدير، معلم، أو طالب جديد للنظام.</p>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم الرباعي</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="أدخل الاسم الكامل" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">الصورة الشخصية (اختياري)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-camera text-muted"></i></span>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="******" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">نوع الحساب (الصلاحية)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-muted"></i></span>
                                <select name="role" class="form-select" required>
                                    <option value="" disabled selected>-- اختر الصلاحية --</option>
                                    <option value="student">👨‍🎓 طالب</option>
                                    <option value="teacher">👨‍🏫 معلم</option>
                                    <option value="parent">👨‍👩‍👦 ولي أمر</option>  
                                </select>
                            </div>
                        </div>

                        {{-- ✅ حقل اختيار الأبناء (مخفي افتراضياً) --}}
                        <div class="col-md-12 mb-3 d-none" id="students-wrapper">
                            <label class="form-label fw-bold">اختر الأبناء (للربط المباشر)</label>
                            <select name="student_ids[]" class="form-select" multiple size="5">
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->name }} - ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-primary">
                                <i class="fas fa-info-circle"></i> يمكنك اختيار أكثر من طالب بالضغط على زر <b>Ctrl</b> (في ويندوز) أو <b>Command</b> (في ماك) أثناء النقر.
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary rounded-pill px-4">
                                <i class="fas fa-arrow-right me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm">
                                <i class="fas fa-save me-1"></i> حفظ المستخدم
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- ✅ سكربت الإظهار والإخفاء --}}
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.querySelector('select[name="role"]');
        const studentsWrapper = document.getElementById('students-wrapper');

        // دالة للتحقق عند التغيير
        roleSelect.addEventListener('change', function() {
            if (this.value === 'parent') {
                studentsWrapper.classList.remove('d-none'); // إظهار
                studentsWrapper.classList.add('animate__animated', 'animate__fadeIn'); // حركة جمالية (اختياري)
            } else {
                studentsWrapper.classList.add('d-none'); // إخفاء
                // تنظيف الاختيارات عند الإخفاء (اختياري)
                const options = studentsWrapper.querySelectorAll('option');
                options.forEach(o => o.selected = false);
            }
        });
    });
</script>