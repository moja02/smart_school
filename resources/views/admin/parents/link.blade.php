@extends('layouts.admin')

@section('content')

<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">ربط الطلاب بأولياء الأمور 👨‍👩‍👧‍👦</h3>
            <p class="mb-0 opacity-75">إدارة العلاقات العائلية وتوزيع الأبناء على الآباء.</p>
        </div>
        <div class="text-primary opacity-25">
            <i class="fas fa-link fa-4x"></i>
        </div>
    </div>
</div>

<div class="row">
    {{-- القسم الأيمن: نموذج الإضافة --}}
    <div class="col-lg-4 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> ربط جديد</h6>
            </div>
            <div class="card-body bg-light">
                <form action="{{ route('admin.parents.storeLink') }}" method="POST">
                    @csrf
                    
                    {{-- اختيار ولي الأمر --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">1. اختر ولي الأمر</label>
                        <select name="parent_id" class="form-select form-select-lg shadow-sm" required>
                            <option value="" disabled selected>-- القائمة --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- اختيار الطلاب (المحدث) --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">2. اختر الأبناء (الطلاب غير المرتبطين)</label>
                        <select name="student_ids[]" class="form-select shadow-sm" multiple style="height: 200px;" required {{ $students->isEmpty() ? 'disabled' : '' }}>
                            @forelse($students as $student)
                                <option value="{{ $student->id }}" class="p-2 border-bottom">
                                    {{ $student->name }} 
                                    @if($student->studentProfile && $student->studentProfile->class)
                                        - ({{ $student->studentProfile->class->name }})
                                    @endif
                                </option>
                            @empty
                                <option disabled class="text-success fw-bold text-center mt-4" style="background: transparent;">
                                    ✅ جميع الطلاب في النظام مرتبطون حالياً.
                                </option>
                            @endforelse
                        </select>
                        <div class="form-text text-primary mt-2">
                            <i class="fas fa-mouse-pointer"></i> اضغط باستمرار على زر <b>Ctrl</b> (أو Command) لتحديد عدة طلاب في آن واحد.
                        </div>
                    </div>

                    <hr>

                    {{-- زر الحفظ يغلق تلقائياً إذا لم يكن هناك طلاب للربط --}}
                    <button type="submit" class="btn btn-primary w-100 btn-lg shadow" {{ $students->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-link me-2"></i> حفظ واربط
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- القسم الأيسر: جدول العلاقات الحالية --}}
    <div class="col-lg-8 mb-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark m-0"><i class="fas fa-list me-1"></i> العائلات المسجلة</h6>
                
                {{-- البحث يتم في نفس الصفحة الحالية --}}
                <form action="{{ url()->current() }}" method="GET" class="d-flex" style="max-width: 250px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="بحث عن ولي أمر..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 40%;">ولي الأمر</th>
                                <th>الأبناء المرتبطين</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parentsWithChildren as $parent)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                            {{ mb_substr($parent->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="#" class="d-block fw-bold text-decoration-none text-dark">{{ $parent->name }}</a>
                                            <span class="small text-muted"><i class="fas fa-envelope me-1"></i>{{ $parent->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @forelse($parent->children as $child)
                                            <div class="badge bg-white text-dark border shadow-sm d-flex align-items-center p-2 rounded-pill">
                                                <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 20px; height: 20px;">
                                                    <i class="fas fa-user-graduate text-primary" style="font-size: 0.7rem;"></i>
                                                </div>
                                                {{ $child->name }}
                                                
                                                {{-- فاصل عمودي صغير --}}
                                                <div class="vr mx-2"></div>

                                                {{-- زر فك الارتباط --}}
                                                <form action="{{ route('admin.parents.deleteLink', $parent->id) }}" method="POST" class="d-inline delete-link-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="student_id" value="{{ $child->id }}">
                                                    <button type="button" class="btn btn-link p-0 text-danger btn-detach" title="إزالة هذا الطالب من ولي الأمر" style="line-height: 1;">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @empty
                                            <span class="text-muted small fst-italic">لا يوجد أبناء مرتبطين</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-5 text-muted">
                                    <div class="opacity-50 mb-3">
                                        <i class="fas fa-users-slash fa-4x"></i>
                                    </div>
                                    <h6 class="fw-bold">لا توجد نتائج</h6>
                                    <p class="small">قم بإضافة رابط جديد من القائمة الجانبية.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- الترقيم (Pagination) --}}
            @if($parentsWithChildren instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white py-3">
                    {{ $parentsWithChildren->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تفعيل SweetAlert لزر فك الارتباط
        const detachButtons = document.querySelectorAll('.btn-detach');
        detachButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.delete-link-form');
                
                Swal.fire({
                    title: 'فك الارتباط؟',
                    text: "سيتم فصل الطالب عن ولي الأمر، لكن لن يتم حذف حساب الطالب.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، افصل',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection