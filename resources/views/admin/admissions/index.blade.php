@extends('layouts.admin')
@section('title', 'طلبات التسجيل الجديدة')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-user-plus text-primary me-2"></i> طلبات القبول والتسجيل</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($admissions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>ولي الأمر</th>
                            <th>رقم الهاتف</th>
                            <th>المرحلة المطلوبة</th>
                            <th>تاريخ الطلب</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admissions as $admission)
                        <tr>
                            <td class="fw-bold">{{ $admission->student_name }}
                                <br><small class="text-muted">مواليد: {{ $admission->student_dob }}</small>
                            </td>
                            <td>{{ $admission->parent_name }}
                                <br><small class="text-muted">{{ $admission->parent_email }}</small>
                            </td>
                            <td>{{ $admission->parent_phone }}</td>
                            <td><span class="badge bg-info text-dark fs-6">{{ $admission->grade->name ?? 'غير محدد' }}</span></td>
                            <td>{{ $admission->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($admission->status == 'pending')
                                    <span class="badge bg-warning text-dark px-3 py-2">قيد المراجعة</span>
                                @elseif($admission->status == 'approved')
                                    <span class="badge bg-success px-3 py-2">تم القبول</span>
                                @else
                                    <span class="badge bg-danger px-3 py-2">مرفوض</span>
                                @endif
                            </td>
                            <td>
                                @if($admission->status == 'pending')
                                <form action="{{ route('admin.admissions.status', $admission->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success fw-bold" title="قبول الطلب"><i class="fas fa-check"></i> قبول</button>
                                </form>
                                <form action="{{ route('admin.admissions.status', $admission->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-sm btn-danger fw-bold" title="رفض الطلب"><i class="fas fa-times"></i> رفض</button>
                                </form>
                                @else
                                    <span class="text-muted">لا توجد إجراءات إضافية</span>
                                @endif
                            </td>
                        </tr>
                        @if($admission->notes)
                        <tr>
                            <td colspan="7" class="text-start bg-light text-muted">
                                <strong>ملاحظات ولي الأمر:</strong> {{ $admission->notes }}
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center p-5 text-muted bg-light rounded">
                <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary"></i>
                <h5>لا توجد طلبات تسجيل حالياً</h5>
                <p>الطلبات الجديدة من البوابة ستظهر هنا للمراجعة.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
