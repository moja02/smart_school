@extends('layouts.admin')

@section('content')

{{-- 1. ترويسة الصفحة --}}
<div class="card page-header-card mb-4 shadow">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">إدارة الشعب الدراسية 🏫</h2>
            <p class="mb-0 opacity-75">يمكنك إضافة شعب جديدة أو تعديل مسميات الشعب الحالية.</p>
        </div>
        <div class="text-end">
            <a href="{{ route('admin.classes') }}" class="btn btn-light shadow-sm text-primary fw-bold">
                <i class="fas fa-list me-2"></i> الجدول الكامل
            </a>
        </div>
    </div>
</div>

<div class="row">
    @forelse($grades as $grade)
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0 h-100 animate__animated animate__fadeIn">
            
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-primary mb-0">
                    <i class="fas fa-layer-group me-2 text-secondary opacity-50"></i> {{ $grade->name }}
                </h5>
            </div>

            <div class="card-body">
                @if($grade->classes->count() > 0)
                    <div class="mb-4">
                        <label class="small text-muted fw-bold mb-2 d-block">الشعب الحالية (تعديل أو حذف):</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($grade->classes as $class)
                                <div class="btn-group shadow-sm" role="group">
                                    {{-- اسم الشعبة --}}
                                    <span class="badge bg-white text-dark border border-end-0 px-3 py-2 fw-bold d-flex align-items-center" style="border-radius: 5px 0 0 5px;">
                                        {{ $class->section }}
                                    </span>
                                    
                                    {{-- زر التعديل --}}
                                    <button type="button" 
                                            class="btn btn-sm btn-info text-white border-0 px-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal{{ $class->id }}"
                                            title="تعديل">
                                        <i class="fas fa-pen fa-xs"></i>
                                    </button>

                                    {{-- زر فتح نافذة الحذف --}}
                                    <button type="button" 
                                            class="btn btn-sm btn-danger text-white border-0 px-2 h-100" 
                                            style="border-radius: 0 5px 5px 0;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $class->id }}"
                                            title="حذف">
                                        <i class="fas fa-trash-alt fa-xs"></i>
                                    </button>
                                </div>

                                {{-- مودال التعديل --}}
                                <div class="modal fade" id="editModal{{ $class->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-light py-2">
                                                    <h6 class="modal-title fw-bold">تعديل اسم الشعبة</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-end">
                                                    <input type="text" name="section" class="form-control text-center fw-bold" value="{{ $class->section }}" required>
                                                </div>
                                                <div class="modal-footer border-0 p-2">
                                                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">حفظ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- مودال تأكيد الحذف الاحترافي --}}
                                <div class="modal fade" id="deleteModal{{ $class->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-danger text-white py-2">
                                                <h6 class="modal-title fw-bold small"><i class="fas fa-exclamation-triangle me-2"></i> تأكيد الحذف</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center py-4">
                                                <p class="mb-1 fw-bold text-dark">هل أنت متأكد من الحذف؟</p>
                                                <span class="badge bg-light text-danger border px-3 py-2 fs-6">{{ $class->section }}</span>
                                                <p class="text-muted small mt-3 mb-0">سيتم فك ارتباط الطلاب بهذه الشعبة نهائياً.</p>
                                            </div>
                                            <div class="modal-footer bg-light border-0 p-2">
                                                <div class="row w-100 g-2">
                                                    <div class="col-6">
                                                        <button type="button" class="btn btn-secondary btn-sm w-100 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                                                    </div>
                                                    <div class="col-6">
                                                        <form action="{{ route('admin.classes.delete', $class->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill shadow-sm">تأكيد</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <hr class="my-4 opacity-25">

                {{-- فورم إضافة شعب جديدة --}}
                <form action="{{ route('admin.classes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="grade_id" value="{{ $grade->id }}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="small fw-bold text-dark"><i class="fas fa-plus-circle text-success me-1"></i> إضافة شعب جديدة:</label>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" onclick="addInput('{{ $grade->id }}')">
                            <i class="fas fa-plus me-1"></i> حقل إضافي
                        </button>
                    </div>
                    
                    <div class="sections-container" id="container-{{ $grade->id }}">
                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-pen text-muted"></i></span>
                            <input type="text" name="sections[]" class="form-control border-start-0" placeholder="مثال: أ، ب، 1..." required>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                            <i class="fas fa-save me-2"></i> حفظ الشعب الجديدة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5"><p class="text-muted">لا توجد صفوف مفعّلة.</p></div>
    @endforelse
</div>

<script>
    function addInput(gradeId) {
        const container = document.getElementById('container-' + gradeId);
        const div = document.createElement('div');
        div.className = 'input-group mb-2 shadow-sm animate__animated animate__fadeInUp'; 
        div.innerHTML = `
            <span class="input-group-text bg-light border-end-0"><i class="fas fa-pen text-muted"></i></span>
            <input type="text" name="sections[]" class="form-control border-start-0" placeholder="اسم الشعبة..." required>
            <button type="button" class="btn btn-white border border-start-0 text-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
        div.querySelector('input').focus();
    }
</script>

@endsection