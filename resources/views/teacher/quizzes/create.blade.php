@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    {{-- عرض رسائل الخطأ (Validation Errors) --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle me-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- عرض رسالة الخطأ القادمة من الكنترولر (مثلاً: عدد الأسئلة غير كافٍ) --}}
    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0 mb-4 fw-bold">
            <i class="fas fa-times-circle me-2"></i> {{ session('error') }}
        </div>
    @endif
    {{-- 1. الترويسة (النمط الداكن) --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-white mb-1">إعداد اختبار جديد</h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-book me-1"></i> المادة: {{ $subject->name }} 
                    <span class="mx-2">|</span> 
                    <i class="fas fa-layer-group me-1"></i> الشعبة: {{ $section->section }}
                </p>
            </div>
            <a href="{{ route('teacher.quizzes.index', ['subject_id' => $subject->id, 'section_id' => $section->id]) }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="fas fa-times me-2"></i> إلغاء
            </a>
        </div>
    </div>

    <form action="{{ route('teacher.quizzes.store') }}" method="POST">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="section_id" value="{{ $section->id }}">

        <div class="row g-4">
            
            {{-- القسم الأيمن: بيانات الاختبار الأساسية --}}
            <div class="col-lg-8">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-file-alt me-2"></i> البيانات الأساسية</h6>
                    </div>
                    <div class="card-body">
                        {{-- عنوان الاختبار --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">عنوان الاختبار <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="مثلاً: اختبار الشهر الأول - الوحدة الأولى" required>
                        </div>

                        {{-- الوصف --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">تعليمات الاختبار (يظهر للطالب قبل البدء)</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="مثلاً: الرجاء قراءة الأسئلة جيداً، ممنوع استخدام الآلة الحاسبة..."></textarea>
                        </div>

                        {{-- المدة --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">مدة الاختبار (بالدقائق) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-stopwatch text-muted"></i></span>
                                <input type="number" name="duration" class="form-control" value="30" min="5" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- القسم الأيسر: إعدادات الإرسال الفوري --}}
            <div class="col-lg-4">
                <div class="card shadow border-0 h-100 bg-light border-primary border-top border-3">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="avatar-lg bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center text-primary" style="width: 70px; height: 70px;">
                                <i class="fas fa-rocket fa-2x"></i>
                            </div>
                            <h5 class="fw-bold text-dark">الإرسال الفوري للطلاب</h5>
                            <p class="text-muted small">سيتم اختيار الأسئلة تلقائياً من بنك الأسئلة ونشر الاختبار فوراً.</p>
                        </div>

                        {{-- إعدادات التوليد التلقائي --}}
                        <div class="card-radio p-3 border rounded bg-white mb-3 shadow-sm">
                            <div class="form-check form-switch">
                                {{--  Checked by default --}}
                                <input class="form-check-input" type="checkbox" name="auto_generate" id="autoGenerate" value="1" checked>
                                <label class="form-check-label fw-bold" for="autoGenerate">سحب أسئلة من البنك</label>
                            </div>
                        </div>

                        {{--  تحديد مصدر الأسئلة --}}
                            <div id="lessonFilterDiv">
                                <label class="form-label small text-muted fw-bold">مصدر الأسئلة:</label>
                                <select name="lesson_id" class="form-select form-select-sm mb-2" id="lessonSelector">
                                    <option value="">-- شامل (من جميع الدروس) --</option>
                                    
                                    {{-- خيار الأسئلة العامة (بدون درس) --}}
                                    @if($generalQuestionsCount > 0)
                                        <option value="general">أسئلة عامة (غير مرتبطة بدرس) - {{ $generalQuestionsCount }} سؤال</option>
                                    @endif

                                    {{-- خيارات الدروس --}}
                                    @foreach($lessons as $lesson)
                                        <option value="{{ $lesson->id }}" {{ $lesson->questions_count == 0 ? 'disabled' : '' }} class="{{ $lesson->questions_count == 0 ? 'text-muted' : 'fw-bold' }}">
                                            {{ $lesson->title }} ({{ $lesson->questions_count }} أسئلة)
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small" id="lessonHint">
                                    اختر الدرس الذي تريد الاختبار منه. الدروس الفارغة لا يمكن اختيارها.
                                </div>
                            </div>


                        {{-- عدد الأسئلة --}}
                        <div id="questionsCountDiv" class="mb-3">
                            <label class="form-label fw-bold small">عدد الأسئلة في الاختبار:</label>
                            <div class="input-group">
                                <input type="number" name="questions_count" class="form-control text-center fw-bold fs-5" value="5" min="1" required>
                                <span class="input-group-text bg-white text-muted">سؤال</span>
                            </div>
                            
                            {{-- عرض عدد الأسئلة المتوفرة في البنك --}}
                            @php
                                // حساب عدد الأسئلة المتوفرة في البنك لهذه المادة والشعبة (التي ليست مرتبطة باختبار)
                                $bankCount = \DB::table('questions')
                                            ->where('subject_id', $subject->id)
                                            ->where('section_id', $section->id)
                                            ->whereNull('quiz_id')
                                            ->count();
                            @endphp
                            <div class="form-text text-success mt-2">
                                <i class="fas fa-check-circle me-1"></i> متوفر في البنك: <strong>{{ $bankCount }}</strong> سؤال جاهز.
                            </div>
                        </div>

                        {{-- زر الحفظ والإرسال --}}
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-3 fw-bold shadow hover-scale rounded-pill" {{ $bankCount == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane me-2"></i> إرسال الاختبار للطلاب
                            </button>
                            @if($bankCount == 0)
                                <small class="text-danger text-center mt-2 fw-bold">يجب إضافة أسئلة للبنك أولاً!</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-3px); }
</style>
@endsection