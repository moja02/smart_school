@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">📅 الجداول الدراسية</h3>
        
        @if(Route::has('admin.schedules.preferences'))
            <a href="{{ route('admin.schedules.preferences') }}" class="btn btn-outline-dark rounded-pill">
                <i class="fas fa-cog me-2"></i> إدارة جميع التفضيلات
            </a>
        @endif
    </div>

    <ul class="nav nav-tabs mb-4" id="scheduleTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">
                <i class="fas fa-users me-2"></i> جدول الفصول
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="teachers-tab" data-bs-toggle="tab" data-bs-target="#teachers" type="button">
                <i class="fas fa-chalkboard-teacher me-2"></i> جدول المعلمين
            </button>
        </li>
    </ul>
    {{-- تأكد من وجود مكتبة SweetAlert2 في الهيدر --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="mb-4">
    <button type="button" id="startAiBtn" class="btn btn-danger btn-lg rounded-pill px-5 shadow">
        <i class="fas fa-robot me-2"></i> توليد الجدول بالذكاء الاصطناعي
    </button>
</div>

<form id="aiForm" action="{{ route('admin.schedules.generate') }}" method="POST" style="display:none;">
    @csrf
</form>

<script>
    document.getElementById('startAiBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'بدء توليد الجدول؟',
            text: "سيقوم النظام بمسح الجدول الحالي وإعادة بنائه وفقاً لتفضيلات المعلمين المتاحة.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، ابدأ الآن',
            cancelButtonText: 'إلغاء',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // نافذة الانتظار
                Swal.fire({
                    title: 'جاري معالجة القيود...',
                    html: 'تقوم الخوارزمية الآن بحساب آلاف الاحتمالات لإيجاد أفضل توزيع.<br><b>يرجى عدم إغلاق الصفحة.</b>',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                // إرسال الطلب
                document.getElementById('aiForm').submit();
            }
        });
    });

    // عرض رسائل النجاح أو الفشل القادمة من السيرفر
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'تمت العملية',
            text: "{{ session('success') }}",
            timer: 4000
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'فشل التوليد',
            text: "{{ session('error') }}",
            confirmButtonText: 'موافق'
        });
    @endif
</script>
    <div class="tab-content" id="myTabContent">
        
        {{-- تبويب الفصول --}}
        <div class="tab-pane fade show active" id="classes" role="tabpanel">
            @foreach($classes as $class)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0">{{ $class->name }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center m-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>اليوم / الحصة</th>
                                    @foreach($periods as $p) <th>الحصة {{ $p }}</th> @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $day)
                                <tr>
                                    <td class="fw-bold bg-light">{{ $day }}</td>
                                    @foreach($periods as $p)
                                        @php
                                            $session = $class->schedules->where('day', $day)->where('period', $p)->first();
                                        @endphp
                                        <td>
                                            @if($session)
                                                <span class="d-block fw-bold text-primary">{{ $session->subject->name ?? '' }}</span>
                                                <small class="text-muted">{{ $session->teacher->name ?? '-' }}</small>
                                            @else -- @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- تبويب المعلمين --}}
        <div class="tab-pane fade" id="teachers" role="tabpanel">
            @foreach($teachers as $teacher)
            <div class="card mb-4 shadow-sm border-start border-4 border-success">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-success"><i class="fas fa-user-tie me-2"></i> الأستاذ: {{ $teacher->name }}</h5>
                    
                    {{-- تم تعديل اسم الرابط هنا ليتوافق مع ما هو متوقع في ملف الراوت --}}
                    @if(Route::has('admin.schedules.edit'))
                        <a href="{{ route('admin.schedules.edit', $teacher->id) }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-user-clock me-1"></i> تعديل التفضيلات
                        </a>
                    @elseif(Route::has('admin.schedules.preferences.edit'))
                         <a href="{{ route('admin.schedules.preferences.edit', $teacher->id) }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-user-clock me-1"></i> تعديل التفضيلات
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center m-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>اليوم / الحصة</th>
                                    @foreach($periods as $p) <th>الحصة {{ $p }}</th> @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $day)
                                <tr>
                                    <td class="fw-bold bg-light">{{ $day }}</td>
                                    @foreach($periods as $p)
                                        @php
                                            $session = $teacher->schedules->where('day', $day)->where('period', $p)->first();
                                        @endphp
                                        <td class="{{ $session ? 'bg-success bg-opacity-10' : '' }}">
                                            @if($session)
                                                <span class="d-block fw-bold">{{ $session->schoolClass->name ?? '' }}</span>
                                                <small class="text-muted">{{ $session->subject->name ?? '' }}</small>
                                            @else -- @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'تم بنجاح!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'عذراً!',
            text: "{{ session('error') }}",
            confirmButtonText: 'حسناً'
        });
    @endif
</script>