@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 text-white" style="background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">⚙️ تفضيلات المعلمين</h2>
                <p class="mb-0 opacity-75">إدارة أوقات العمل المفضلة قبل البدء في توزيع الجدول.</p>
            </div>
            <a href="{{ route('admin.schedules.view') }}" class="btn btn-light rounded-pill px-4 fw-bold">العودة للجداول</a>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">المعلم</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $teacher->name }}</td>
                            <td class="text-center">
                                @php $hasPrefs = \App\Models\TeacherPreference::where('teacher_id', $teacher->id)->exists(); @endphp
                                <span class="badge rounded-pill {{ $hasPrefs ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $hasPrefs ? 'تم الضبط' : 'لم يتم الضبط' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.schedules.preferences.edit', $teacher->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i> ضبط التفضيلات
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
@endsection