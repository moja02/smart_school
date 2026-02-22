@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 text-white" style="background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">⚙️ إدارة تفضيلات المعلمين</h2>
                <p class="mb-0 opacity-75">عرض وتعديل أوقات العمل المفضلة لكل معلم قبل توليد الجدول.</p>
            </div>
            <a href="{{ route('admin.schedules.view') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="fas fa-calendar-alt me-2"></i> عرض الجدول العام
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 fw-bold text-dark"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i> قائمة المعلمين</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th class="text-center">حالة التفضيلات</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                        <tr>
                            <td class="fw-bold">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ substr($teacher->name, 0, 1) }}
                                    </div>
                                    {{ $teacher->name }}
                                </div>
                            </td>
                            <td>{{ $teacher->email }}</td>
                            <td class="text-center">
                                @php
                                    $hasPrefs = \App\Models\TeacherPreference::where('teacher_id', $teacher->id)->exists();
                                @endphp
                                @if($hasPrefs)
                                    <span class="badge bg-success-soft text-success border border-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> تم الضبط
                                    </span>
                                @else
                                    <span class="badge bg-warning-soft text-warning border border-warning px-3 py-2 rounded-pill">
                                        <i class="fas fa-exclamation-triangle me-1"></i> لم يتم الضبط
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.schedules.edit', $teacher->id) }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                                    <i class="fas fa-user-clock me-1"></i> ضبط الأوقات
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: #e8f5e9; }
    .bg-warning-soft { background-color: #fffde7; }
    .table thead th { border-top: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
    .btn-sm { font-size: 0.8rem; }
</style>
@endsection