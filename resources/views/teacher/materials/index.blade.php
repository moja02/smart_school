@extends('layouts.teacher')
@section('title', 'المذكرات والملفات الدراسية')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
    <script>Swal.fire({ icon: 'success', title: 'تمت العملية', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });</script>
@endif
@if(session('error'))
    <script>Swal.fire({ icon: 'error', title: 'خطأ', text: "{{ session('error') }}", timer: 3000, showConfirmButton: true });</script>
@endif

<div class="container py-4">

    {{-- 1. الترويسة --}}
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-white">📂 المذكرات والملفات الدراسية</h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-book me-1"></i> {{ $subject->name }}
                    <span class="mx-2">|</span>
                    <i class="fas fa-layer-group me-1"></i> الشعبة: {{ $class->section }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light text-dark fw-bold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-cloud-upload-alt me-2"></i> رفع ملف جديد
                </button>
                <a href="{{ route('teacher.class.show', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}" class="btn btn-outline-light btn-sm shadow-sm px-3 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i> العودة للفصل
                </a>
            </div>
        </div>
    </div>

    {{-- 2. قائمة الملفات المرفوعة --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="m-0 fw-bold text-dark"><i class="fas fa-folder-open text-warning me-2"></i> الملفات المرفوعة ({{ $materials->count() }})</h5>
        </div>
        <div class="card-body p-0">
            @if($materials->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>الملف</th>
                            <th>الوصف</th>
                            <th>النوع</th>
                            <th>الحجم</th>
                            <th>تاريخ الرفع</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materials as $index => $material)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3 fs-4">
                                        <i class="{{ $material->file_icon }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $material->title }}</h6>
                                        <small class="text-muted">{{ $material->file_name }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ Str::limit($material->description, 40) ?: '--' }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill text-uppercase">{{ $material->file_type }}</span>
                            </td>
                            <td class="text-muted">{{ $material->readable_file_size }}</td>
                            <td>
                                <small class="text-muted">{{ $material->created_at->format('Y-m-d') }}</small>
                                <br>
                                <small class="text-muted opacity-75">{{ $material->created_at->format('h:i A') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- زر تحميل للمعاينة --}}
                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="معاينة">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- زر حذف --}}
                                    <form action="{{ route('teacher.materials.delete', $material->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟')" title="حذف">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <div class="opacity-25 mb-3"><i class="fas fa-cloud-upload-alt fa-4x text-muted"></i></div>
                <h5 class="fw-bold text-secondary">لا توجد ملفات مرفوعة حتى الآن</h5>
                <p class="text-muted">قم برفع مذكرات، ملازم، أوراق عمل أو أي ملفات تعليمية ليتمكن الطلاب من تحميلها ودراستها.</p>
                <button class="btn btn-primary rounded-pill px-5 mt-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-cloud-upload-alt me-2"></i> رفع أول ملف
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal رفع ملف جديد --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="uploadModalLabel"><i class="fas fa-cloud-upload-alt me-2"></i> رفع ملف دراسي جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('teacher.materials.store', ['subject_id' => $subject->id, 'section_id' => $class->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- عنوان الملف --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">عنوان الملف <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" required placeholder="مثال: مذكرة الوحدة الأولى" value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- وصف الملف --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">وصف / ملاحظات <small class="text-muted">(اختياري)</small></label>
                        <textarea name="description" id="description" class="form-control" rows="2" placeholder="مثال: يحتوي على ملخص الباب الثاني...">{{ old('description') }}</textarea>
                    </div>

                    {{-- اختيار الملف --}}
                    <div class="mb-3">
                        <label for="file" class="form-label fw-bold">اختر الملف <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" required
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar,.mp4,.mp3">
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            الأنواع المسموحة: PDF, Word, Excel, PowerPoint, صور, مقاطع فيديو, مضغوط.
                            <br>الحد الأقصى: <strong>20 ميجابايت</strong>.
                        </div>
                    </div>

                    {{-- معاينة الملف المختار --}}
                    <div id="filePreview" class="d-none alert alert-light border mb-0 py-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt text-primary me-2 fs-5"></i>
                            <div>
                                <span id="fileName" class="fw-bold text-dark"></span>
                                <br><small id="fileSize" class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-upload me-1"></i> رفع الملف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // معاينة الملف المختار قبل الرفع
    document.getElementById('file').addEventListener('change', function() {
        const preview = document.getElementById('filePreview');
        const nameSpan = document.getElementById('fileName');
        const sizeSpan = document.getElementById('fileSize');

        if (this.files.length > 0) {
            const file = this.files[0];
            nameSpan.textContent = file.name;
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            sizeSpan.textContent = sizeMB + ' MB';
            preview.classList.remove('d-none');

            if (file.size > 20 * 1024 * 1024) {
                sizeSpan.innerHTML = '<span class="text-danger fw-bold">' + sizeMB + ' MB - الملف كبير جداً!</span>';
            }
        } else {
            preview.classList.add('d-none');
        }
    });
</script>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-3px); }
</style>
@endsection
