@extends('layouts.admin') {{-- تأكد من اسم ملف الليآوت الصحيح لديك --}}

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            {{-- ترويسة الصفحة --}}
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary">رصد الدرجات 📝</h3>
                <p class="text-muted">للطالب: <span class="text-dark fw-bold">{{ $student->user->name }}</span></p>
            </div>

            {{-- بطاقة النموذج --}}
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    
                    <form action="{{ route('teacher.storeGrade', $student->id) }}" method="POST">
                        @csrf
                        
                        {{-- حقل اختيار المادة --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">المادة الدراسية</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-book text-primary"></i></span>
                                <select name="subject" class="form-select border-start-0 bg-light" required>
                                    <option value="">-- اختر المادة --</option>
                                    {{-- ملاحظة: هنا يجب أن نمرر المواد التي يدرسها هذا المعلم لهذا الطالب --}}
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject }}">{{ $subject }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- حقل إدخال الدرجة --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">الدرجة المستحقة (من 100)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-star text-warning"></i></span>
                                <input type="number" name="total_score" class="form-control border-start-0 bg-light" placeholder="مثلاً: 95" min="0" max="100" required>
                            </div>
                        </div>

                        {{-- أزرار التحكم --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm hover-effect">
                                <i class="fas fa-save"></i> حفظ الدرجة
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill">
                                إلغاء ورجوع
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* تأثيرات إضافية خاصة بهذه الصفحة */
    .form-control:focus, .form-select:focus {
        box-shadow: none;
        border-color: #3d5ee1;
        background-color: #fff;
    }
    .input-group-text { border-color: #ced4da; }
    .hover-effect { transition: transform 0.2s; }
    .hover-effect:hover { transform: translateY(-2px); }
</style>

@endsection