@extends('layouts.teacher')
@section('title', 'إدارة الواجبات')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-book-open text-primary me-2"></i> الواجبات المدرسية: {{ $subject->name }} - {{ $class->section }}</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHomeworkModal">
            <i class="fas fa-plus"></i> إضافة واجب جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($homeworks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>العنوان</th>
                            <th>الوصف</th>
                            <th>تاريخ التسليم</th>
                            <th>تاريخ الإضافة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($homeworks as $hw)
                        <tr>
                            <td class="fw-bold">{{ $hw->title }}</td>
                            <td class="text-muted">{{ Str::limit($hw->description, 50) }}</td>
                            <td>
                                <span class="badge {{ \Carbon\Carbon::parse($hw->due_date)->isPast() ? 'bg-danger' : 'bg-success' }}">
                                    {{ \Carbon\Carbon::parse($hw->due_date)->format('Y-m-d') }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $hw->created_at->format('Y-m-d') }}</small></td>
                            <td>
                                <form action="{{ route('teacher.homeworks.destroy', $hw->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الواجب؟')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center p-5 text-muted bg-light rounded">
                <i class="fas fa-clipboard-list fs-1 mb-3 text-secondary"></i>
                <h5>لا توجد واجبات مضافة لهذا الفصل</h5>
                <p>قم بإضافة واجب جديد باستخدام الزر أعلاه ليظهر للطلاب وأولياء أمورهم.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addHomeworkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">إضافة واجب جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teacher.homeworks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="class_id" value="{{ $class->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">عنوان الواجب</label>
                        <input type="text" name="title" class="form-control" required placeholder="مثال: حل تدريبات الباب الأول">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف / الملاحظات</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="مثال: التركيز على الصفحة 15 و 16"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ التسليم (آخر موعد)</label>
                        <input type="date" name="due_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ ونشر الواجب</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
