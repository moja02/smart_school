@extends('layouts.parent')

@section('title', 'بيانات الابناء')

@section('content')

    <div class="page-header-card p-4 text-white shadow mb-4">
        <h3 class="mb-1">بيانات الابناء</h3>
        <p class="mb-0">
            عرض بيانات كل ابن مرتبط بحساب ولي الامر.
        </p>
    </div>

    @if(isset($children) && $children->count())
        <div class="row g-4">
            @foreach($children as $child)
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                {{ $child->name ?? 'بدون اسم' }}
                            </h5>
                            <p class="text-muted mb-2">
                                الصف: {{ $child->class_name ?? 'غير محدد' }}
                                @if(!empty($child->section))
                                    - الشعبة: {{ $child->section }}
                                @endif
                            </p>

                            <dl class="row mb-0 small">
                                <dt class="col-sm-4">رقم الطالب</dt>
                                <dd class="col-sm-8">{{ $child->student_code ?? '-' }}</dd>

                                <dt class="col-sm-4">الرقم الوطني</dt>
                                <dd class="col-sm-8">{{ $child->national_id ?? '-' }}</dd>

                                <dt class="col-sm-4">تاريخ الميلاد</dt>
                                <dd class="col-sm-8">{{ $child->birth_date ?? '-' }}</dd>

                                <dt class="col-sm-4">النوع</dt>
                                <dd class="col-sm-8">{{ $child->gender ?? '-' }}</dd>
                            </dl>

                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('parent.grades') }}"
                                   class="btn btn-outline-primary btn-sm">
                                    عرض الدرجات
                                </a>
                                <a href="{{ route('parent.attendance') }}"
                                   class="btn btn-outline-success btn-sm">
                                    متابعة الحضور
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            لا يوجد ابناء مسجلون على هذا الحساب حاليا.
        </div>
    @endif

@endsection
