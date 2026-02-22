@extends('layouts.parent')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">مرحباً، {{ Auth::user()->name }} 👋</h2>
            <p class="mb-0 opacity-75">تابع المستوى الدراسي لأبنائك وتواصل مع المدرسة.</p>
        </div>
        <i class="fas fa-user-friends fa-4x opacity-25"></i>
    </div>
</div>

@forelse($children as $child)
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="avatar-sm bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">
                {{ substr($child->user->name, 0, 1) }}
            </div>
            <div>
                <h5 class="m-0 fw-bold text-dark">{{ $child->user->name }}</h5>
                <small class="text-muted"><i class="fas fa-chalkboard me-1"></i> {{ $child->schoolClass->name ?? 'غير محدد' }}</small>
            </div>
        </div>
        <a href="{{ route('messages.chat', $child->user->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
            <i class="fas fa-comment-dots me-1"></i> محادثة الابن
        </a>
    </div>
    
    <div class="card-body">
        <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-file-invoice text-warning me-2"></i> كشف الدرجات</h6>
        
        @if($child->grades->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>المادة</th>
                            <th class="text-center">الدرجة</th>
                            <th class="text-center">التقدير</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($child->grades as $grade)
                        <tr>
                            <td class="fw-bold">{{ $grade->subject->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill px-3">{{ $grade->total_score }}</span>
                            </td>
                            <td class="text-center">
                                @if($grade->total_score >= 90) <span class="text-success fw-bold">ممتاز</span>
                                @elseif($grade->total_score >= 80) <span class="text-primary fw-bold">جيد جداً</span>
                                @elseif($grade->total_score >= 50) <span class="text-secondary fw-bold">ناجح</span>
                                @else <span class="text-danger fw-bold">راسب</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light text-center border-0">
                لا توجد درجات مرصودة لهذا الطالب حتى الآن.
            </div>
        @endif
    </div>
</div>
@empty
<div class="text-center py-5">
    <i class="fas fa-child fa-4x text-muted opacity-25 mb-3"></i>
    <h4 class="text-muted">لا يوجد طلاب مرتبطين بحسابك حالياً.</h4>
    <p class="small text-muted">يرجى التواصل مع إدارة المدرسة لربط حسابك بأبنائك.</p>
</div>
@endforelse

@endsection