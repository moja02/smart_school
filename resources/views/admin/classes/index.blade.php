@extends('layouts.admin')

@section('content')

{{-- 1. ترويسة الصفحة بنفس ستايل الداشبورد --}}
<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">إدارة الفصول الدراسية 🏫</h2>
            <p class="mb-0 opacity-75">نظرة عامة على الهيكل الدراسي، توزيع الشعب، وإحصائيات الطلاب لكل صف.</p>
        </div>
        <div class="text-end d-flex gap-2">
            {{-- زر النقل الجماعي --}}
            <a href="{{ route('admin.students') }}" class="btn btn-warning shadow-sm fw-bold text-dark">
                <i class="fas fa-users-cog me-2"></i> نقل وتوزيع الطلاب
            </a>
            {{-- زر الإضافة --}}
            <a href="{{ route('admin.classes.create') }}" class="btn btn-light shadow-sm text-primary fw-bold">
                <i class="fas fa-plus-circle me-2"></i> إضافة فصول جديدة
            </a>
        </div>
    </div>
</div>

{{-- 2. عرض الصفوف بنظام البطاقات المطور --}}
@if($grades->count() > 0)
    <div class="row">
        @foreach($grades as $grade)
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card shadow border-0 h-100 animate__animated animate__fadeIn">
                
                {{-- رأس البطاقة: اسم الصف --}}
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="fas fa-layer-group me-2 text-secondary opacity-50"></i> {{ $grade->name }}
                    </h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                        {{ $grade->classes->count() }} شعبة
                    </span>
                </div>

                {{-- جسم البطاقة: قائمة الشعب --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4 py-3">الشعبة</th>
                                    <th class="text-center">عدد الطلاب</th>
                                    <th class="text-end pe-4">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grade->classes as $class)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            {{ $class->section }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3">
                                            {{ $class->students_count ?? 0 }} طالب
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.classes.delete', $class->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الشعبة ({{ $class->section }})؟ سيتم فك ارتباط الطلاب بها!');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-2" title="حذف الشعبة">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- تذييل البطاقة: إجمالي طلاب الصف --}}
                <div class="card-footer bg-light border-top-0 py-3 text-center">
                    <div class="small fw-bold text-secondary">
                        إجمالي طلاب الصف: 
                        <span class="text-primary fs-6 ms-1">{{ $grade->classes->sum('students_count') }}</span>
                    </div>
                </div>

            </div>
        </div>
        @endforeach
    </div>
@else
    {{-- حالة عدم وجود بيانات (Empty State) --}}
    <div class="card shadow border-0 py-5">
        <div class="card-body text-center py-5">
            <div class="mb-4 opacity-25">
                <i class="fas fa-school fa-5x text-muted"></i>
            </div>
            <h4 class="fw-bold text-secondary">لا توجد فصول دراسية مضافة بعد</h4>
            <p class="text-muted mb-4">ابدأ بتنظيم هيكل مدرستك وإضافة الشعب الدراسية لكل صف الآن.</p>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                <i class="fas fa-plus-circle me-2"></i> إضافة أول فصل
            </a>
        </div>
    </div>
@endif

@endsection