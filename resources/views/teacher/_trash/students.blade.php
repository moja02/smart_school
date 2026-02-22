@extends('layouts.teacher')

@section('title', 'قائمة الطلاب')

@section('content')

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            {{-- هنا نعرض اسم الفصل القادم من الكنترولر --}}
            <h3 class="page-title">طلاب فصل: <span class="text-primary">{{ $class->name }}</span> 📚</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">قائمة الطلاب</li>
            </ul>
        </div>
        <div class="col-auto">
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> رجوع
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>اسم الطالب</th>
                                <th>البريد الإلكتروني</th>
                                <th class="text-center">إجراءات الرصد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2 bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 35px; height: 35px;">
                                            {{ substr($student->user->name, 0, 1) }}
                                        </div>
                                        <div class="fw-bold">{{ $student->user->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $student->user->email }}</td>
                                <td class="text-center">
                                    {{-- زر رصد الدرجة المهم --}}
                                    <a href="{{ route('teacher.createGrade', $student->id) }}" class="btn btn-sm btn-primary px-3 rounded-pill">
                                        <i class="fas fa-edit"></i> رصد الدرجة
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i><br>
                                    لا يوجد طلاب مسجلين في هذا الفصل حالياً.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection