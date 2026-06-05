@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    {{-- 1. ترويسة الصفحة بنفس ستايل الداشبورد --}}
    <div class="card page-header-card mb-4 shadow border-0">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1 text-white">📅 الجدول الدراسي (الذكاء الاصطناعي)</h2>
                <p class="fw-bold mb-1 text-white">عرض الجداول الدراسية وتوليدها آلياً بنقرة زر.</p>
            </div>
            <div class="text-end d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.schedules.preferences') ?? '#' }}" class="btn btn-light shadow-sm text-primary fw-bold">
                    <i class="fas fa-user-cog me-2"></i> تفضيلات الأساتذة
                </a>
                
                {{-- 🔄 زر وضع التبديل --}}
                <button type="button" id="swapModeBtn" class="btn btn-outline-light shadow-sm fw-bold" onclick="toggleSwapMode()">
                    <i class="fas fa-exchange-alt me-2"></i> وضع التبديل
                </button>

                <form action="{{ route('admin.schedules.generate') }}" method="POST" class="d-inline" onsubmit="document.getElementById('generateBtn').disabled = true; document.getElementById('generateSpinner').classList.remove('d-none');">
                    @csrf
                    <button type="submit" id="generateBtn" class="btn btn-warning shadow-sm fw-bold text-dark">
                        <i class="fas fa-robot me-2"></i> توليد الجدول الآن
                        <span id="generateSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 🔄 شريط حالة التبديل --}}
    <div id="swapStatusBar" class="alert border-0 shadow-sm mb-4 d-none" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 1rem;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="fas fa-exchange-alt me-2 fa-lg"></i>
                <strong id="swapStatusText">🔄 وضع التبديل مُفعّل — انقر على الحصة الأولى لاختيارها</strong>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="swapSelection" class="badge bg-white text-dark px-3 py-2 d-none" style="font-size: 0.85rem;"></span>
                <button class="btn btn-sm btn-light fw-bold rounded-pill px-3" onclick="cancelSwapMode()">
                    <i class="fas fa-times me-1"></i> إلغاء
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</div>
    @endif

    <ul class="nav nav-pills mb-4 gap-2" id="scheduleTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold px-4" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes-schedule" type="button" role="tab"><i class="fas fa-chalkboard me-2"></i> جداول الفصول</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold px-4" id="teachers-tab" data-bs-toggle="tab" data-bs-target="#teachers-schedule" type="button" role="tab"><i class="fas fa-chalkboard-teacher me-2"></i> جداول الأساتذة</button>
        </li>
    </ul>

    <div class="tab-content" id="scheduleTabContent">
        <div class="tab-pane fade show active" id="classes-schedule" role="tabpanel">
            @forelse($classes as $class)
                <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <h5 class="fw-bold text-primary mb-0"><i class="fas fa-door-open me-2 text-secondary opacity-50"></i> فصل: {{ $class->name }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover text-center align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="py-3">اليوم / الحصة</th>
                                        @foreach($periods as $period) <th>الحصة {{ $period }}</th> @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($days as $day)
                                        <tr>
                                            <td class="fw-bold bg-light">{{ $day }}</td>
                                            @foreach($periods as $period)
                                                @php
                                                    $schedule = $class->schedules->where('day', $day)->where('period', $period)->first();
                                                @endphp
                                                <td class="schedule-cell {{ $schedule ? 'has-schedule' : '' }}"
                                                    @if($schedule)
                                                        data-schedule-id="{{ $schedule->id }}"
                                                        data-subject="{{ $schedule->subject->name ?? 'مادة' }}"
                                                        data-teacher="{{ $schedule->teacher->name ?? 'أستاذ' }}"
                                                        data-class="{{ $class->name }}"
                                                        data-day="{{ $day }}"
                                                        data-period="{{ $period }}"
                                                        onclick="handleCellClick(this)"
                                                        style="cursor: default;"
                                                    @endif
                                                >
                                                    @if($schedule)
                                                        <div class="fw-bold text-dark">{{ $schedule->subject->name ?? 'مادة' }}</div>
                                                        <div class="small text-muted">{{ $schedule->teacher->name ?? 'أستاذ' }}</div>
                                                    @else
                                                        <span class="text-black-50">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card shadow border-0 py-5">
                    <div class="card-body text-center py-5">
                        <div class="mb-4 opacity-25">
                            <i class="fas fa-calendar-times fa-5x text-muted"></i>
                        </div>
                        <h4 class="fw-bold text-secondary">لم يتم توليد أي جداول للفصول بعد</h4>
                        <p class="text-muted mb-4">اضغط على زر توليد الجدول في الأعلى لإنشاء الجداول آلياً.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="tab-pane fade" id="teachers-schedule" role="tabpanel">
            @forelse($teachers as $teacher)
                <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <h5 class="fw-bold text-primary mb-0"><i class="fas fa-user-tie me-2 text-secondary opacity-50"></i> أستاذ: {{ $teacher->name }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover text-center align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="py-3">اليوم / الحصة</th>
                                        @foreach($periods as $period) <th>الحصة {{ $period }}</th> @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($days as $day)
                                        <tr>
                                            <td class="fw-bold bg-light">{{ $day }}</td>
                                            @foreach($periods as $period)
                                                @php
                                                    $schedule = $teacher->schedules->where('day', $day)->where('period', $period)->first();
                                                @endphp
                                                <td>
                                                    @if($schedule)
                                                        <div class="fw-bold text-dark">{{ $schedule->subject->name ?? 'مادة' }}</div>
                                                        <div class="small text-muted">{{ $schedule->schoolClass->name ?? 'فصل' }}</div>
                                                    @else
                                                        <span class="text-black-50">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                 <div class="card shadow border-0 py-5">
                    <div class="card-body text-center py-5">
                        <div class="mb-4 opacity-25">
                            <i class="fas fa-calendar-times fa-5x text-muted"></i>
                        </div>
                        <h4 class="fw-bold text-secondary">لم يتم توليد أي جداول للأساتذة بعد</h4>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    /* ===== أنماط وضع التبديل ===== */
    .swap-mode .schedule-cell.has-schedule {
        cursor: pointer !important;
        transition: all 0.2s ease;
    }
    .swap-mode .schedule-cell.has-schedule:hover {
        background: rgba(102, 126, 234, 0.1) !important;
        transform: scale(1.03);
        box-shadow: inset 0 0 0 2px #667eea;
        border-radius: 0.5rem;
    }
    .schedule-cell.swap-selected {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15)) !important;
        box-shadow: inset 0 0 0 3px #667eea !important;
        border-radius: 0.5rem;
        position: relative;
    }
    .schedule-cell.swap-selected::after {
        content: '✓';
        position: absolute;
        top: 2px;
        left: 6px;
        font-size: 0.7rem;
        background: #667eea;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    #swapModeBtn.active {
        background: linear-gradient(135deg, #667eea, #764ba2) !important;
        border-color: transparent !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .swap-processing {
        pointer-events: none;
        opacity: 0.6;
    }

    /* أنيميشن لشريط الحالة */
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #swapStatusBar:not(.d-none) {
        animation: slideDown 0.3s ease;
    }
</style>
@endsection

@section('scripts')
<script>
    // ========================================
    // 🔄 نظام تبديل الحصص بين الأساتذة
    // ========================================
    
    let swapMode = false;
    let selectedScheduleA = null;

    const CSRF_TOKEN = '{{ csrf_token() }}';
    const CHECK_SWAP_URL = '{{ route("admin.schedules.check_swap") }}';
    const SWAP_URL = '{{ route("admin.schedules.swap") }}';

    /**
     * تفعيل/إلغاء وضع التبديل
     */
    function toggleSwapMode() {
        swapMode = !swapMode;
        const btn = document.getElementById('swapModeBtn');
        const statusBar = document.getElementById('swapStatusBar');
        const body = document.querySelector('.tab-content');

        if (swapMode) {
            btn.classList.add('active');
            statusBar.classList.remove('d-none');
            body.classList.add('swap-mode');
            resetSwapSelection();
        } else {
            cancelSwapMode();
        }
    }

    /**
     * إلغاء وضع التبديل
     */
    function cancelSwapMode() {
        swapMode = false;
        const btn = document.getElementById('swapModeBtn');
        const statusBar = document.getElementById('swapStatusBar');
        const body = document.querySelector('.tab-content');

        btn.classList.remove('active');
        statusBar.classList.add('d-none');
        body.classList.remove('swap-mode');
        resetSwapSelection();
    }

    /**
     * إعادة تعيين الاختيار
     */
    function resetSwapSelection() {
        selectedScheduleA = null;
        document.querySelectorAll('.swap-selected').forEach(el => el.classList.remove('swap-selected'));
        document.getElementById('swapStatusText').innerHTML = '🔄 وضع التبديل مُفعّل — انقر على الحصة الأولى لاختيارها';
        document.getElementById('swapSelection').classList.add('d-none');
    }

    /**
     * معالجة النقر على خانة في الجدول
     */
    function handleCellClick(cell) {
        if (!swapMode) return;
        
        const scheduleId = cell.getAttribute('data-schedule-id');
        if (!scheduleId) return;

        // إذا لم يتم اختيار الحصة الأولى بعد
        if (!selectedScheduleA) {
            selectedScheduleA = {
                id: scheduleId,
                subject: cell.getAttribute('data-subject'),
                teacher: cell.getAttribute('data-teacher'),
                className: cell.getAttribute('data-class'),
                day: cell.getAttribute('data-day'),
                period: cell.getAttribute('data-period'),
                element: cell
            };
            cell.classList.add('swap-selected');
            
            document.getElementById('swapStatusText').innerHTML = '🔄 تم اختيار الحصة الأولى — الآن انقر على الحصة الثانية للتبديل';
            const selBadge = document.getElementById('swapSelection');
            selBadge.classList.remove('d-none');
            selBadge.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> ${selectedScheduleA.subject} (${selectedScheduleA.teacher}) — ${selectedScheduleA.day} ح${selectedScheduleA.period}`;
            
            return;
        }

        // إذا نقر على نفس الخانة المحددة
        if (scheduleId === selectedScheduleA.id) {
            resetSwapSelection();
            return;
        }

        // اختار الحصة الثانية — نتحقق ثم نبدل
        const scheduleB = {
            id: scheduleId,
            subject: cell.getAttribute('data-subject'),
            teacher: cell.getAttribute('data-teacher'),
            className: cell.getAttribute('data-class'),
            day: cell.getAttribute('data-day'),
            period: cell.getAttribute('data-period'),
            element: cell
        };

        cell.classList.add('swap-selected');
        checkAndSwap(selectedScheduleA, scheduleB);
    }

    /**
     * التحقق من إمكانية التبديل ثم تنفيذه
     */
    async function checkAndSwap(schedA, schedB) {
        document.getElementById('swapStatusText').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري التحقق من التعارضات...';

        try {
            // 1. التحقق من إمكانية التبديل
            const checkResponse = await fetch(CHECK_SWAP_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    schedule_a_id: schedA.id,
                    schedule_b_id: schedB.id
                })
            });

            const checkResult = await checkResponse.json();

            if (!checkResult.valid) {
                // ❌ يوجد تعارض — عرض رسالة خطأ
                let conflictHtml = `<strong>${checkResult.message}</strong>`;
                if (checkResult.conflicts && checkResult.conflicts.length > 0) {
                    conflictHtml += '<ul class="text-start mt-3 mb-0" style="list-style: none; padding: 0;">';
                    checkResult.conflicts.forEach(c => {
                        conflictHtml += `<li class="mb-2"><i class="fas fa-exclamation-circle text-danger me-2"></i>${c}</li>`;
                    });
                    conflictHtml += '</ul>';
                }

                Swal.fire({
                    icon: 'error',
                    title: '❌ تعارض في الجدول',
                    html: conflictHtml,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'فهمت'
                });
                resetSwapSelection();
                return;
            }

            // ✅ التبديل متاح — عرض نافذة تأكيد
            const d = checkResult.details;
            const confirmResult = await Swal.fire({
                title: '🔄 تأكيد التبديل',
                html: `
                    <div style="text-align: right; font-size: 0.95rem;">
                        <div class="p-3 mb-3 rounded-3" style="background: #f0f4ff; border-right: 4px solid #667eea;">
                            <div class="fw-bold text-primary mb-1"><i class="fas fa-arrow-left me-1"></i> الحصة الأولى:</div>
                            <div><strong>${d.a.subject}</strong> — ${d.a.teacher}</div>
                            <div class="small text-muted">${d.a.class} | ${d.a.day} — الحصة ${d.a.period}</div>
                        </div>
                        <div class="text-center my-2"><i class="fas fa-exchange-alt fa-lg" style="color: #667eea;"></i></div>
                        <div class="p-3 rounded-3" style="background: #f5f0ff; border-right: 4px solid #764ba2;">
                            <div class="fw-bold" style="color: #764ba2;"><i class="fas fa-arrow-right me-1"></i> الحصة الثانية:</div>
                            <div><strong>${d.b.subject}</strong> — ${d.b.teacher}</div>
                            <div class="small text-muted">${d.b.class} | ${d.b.day} — الحصة ${d.b.period}</div>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-1"></i> تبديل',
                cancelButtonText: '<i class="fas fa-times me-1"></i> إلغاء',
                reverseButtons: true
            });

            if (!confirmResult.isConfirmed) {
                resetSwapSelection();
                return;
            }

            // 2. تنفيذ التبديل
            document.getElementById('swapStatusText').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري تنفيذ التبديل...';

            const swapResponse = await fetch(SWAP_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    schedule_a_id: schedA.id,
                    schedule_b_id: schedB.id
                })
            });

            const swapResult = await swapResponse.json();

            if (swapResult.success) {
                await Swal.fire({
                    icon: 'success',
                    title: '✅ تم التبديل بنجاح!',
                    text: swapResult.message,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'ممتاز',
                    timer: 2000,
                    timerProgressBar: true
                });
                // تحديث الصفحة لعرض التغييرات
                window.location.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'فشل التبديل',
                    text: swapResult.message,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'حسناً'
                });
                resetSwapSelection();
            }

        } catch (error) {
            console.error('Swap error:', error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ في الاتصال',
                text: 'حدث خطأ أثناء الاتصال بالخادم. حاول مرة أخرى.',
                confirmButtonColor: '#dc3545'
            });
            resetSwapSelection();
        }
    }
</script>
@endsection