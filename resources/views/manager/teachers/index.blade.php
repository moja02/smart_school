@extends('layouts.manager')

@section('content')

{{-- ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">سجل المعلمين 👨‍🏫</h3>
            <p class="mb-0 opacity-75">قائمة بجميع أعضاء الهيئة التدريسية في المدرسة.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-chalkboard-teacher fa-4x opacity-25"></i>
        </div>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i> القائمة الحالية</h6>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary rounded-pill">{{ $teachers->total() }} معلم</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4" width="5%">#</th>
                        <th class="py-3">اسم المعلم</th>
                        <th class="py-3">البريد الإلكتروني</th>
                        <th class="py-3">تاريخ الانضمام</th>
                        <th class="py-3 text-center">الحالة</th>
                        <th class="py-3 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $teacher->name }}</h6>
                                    <small class="text-muted" style="font-size: 11px;">ID: {{ $teacher->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:{{ $teacher->email }}" class="text-decoration-none text-muted">
                                <i class="far fa-envelope me-1"></i> {{ $teacher->email }}
                            </a>
                        </td>
                        <td>
                            <span class="text-muted small">
                                <i class="far fa-calendar-alt me-1"></i> {{ $teacher->created_at->format('Y-m-d') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">نشط</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary" title="عرض الملف الشخصي (قريباً)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="opacity-50">
                                <i class="fas fa-chalkboard-teacher fa-3x mb-3 text-muted"></i>
                                <p class="text-muted fw-bold">لا يوجد معلمين مسجلين حالياً.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- التصفح (Pagination) --}}
    @if($teachers->hasPages())
    <div class="card-footer bg-white py-3">
        <div class="d-flex justify-content-center">
            {{ $teachers->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection