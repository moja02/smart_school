@extends('layouts.teacher')

@section('content')

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<div class="container-fluid py-4">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold"><i class="far fa-calendar-alt me-2"></i>جدول الامتحانات</h5>
                <small class="opacity-75">المادة: {{ $subject->name }} | الشعبة: {{ $section->name }}</small>
            </div>
            {{--زر العودة للشعبة --}}
            <a href="{{ route('teacher.class.show', ['subject_id' => $subject->id, 'class_id' => $section->id]) }}" class="btn btn-light btn-sm rounded-pill text-primary fw-bold">
                <i class="fas fa-arrow-right me-1"></i> عودة للشعبة
            </a>
        </div>
        <div class="card-body">
            
            {{-- دليل المواعيد للشعبة --}}
            <div class="d-flex align-items-center mb-4 p-3 bg-light rounded border-start border-primary border-4 flex-wrap gap-3">
                <span class="text-muted fw-bold">دليل المواعيد:</span>
                
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary rounded-circle p-2 me-2"> </span>
                    <small class="fw-bold text-dark">امتحاناتي ({{ $subject->name }})</small>
                </div>
                
                <div class="d-flex align-items-center">
                    <span class="badge bg-info rounded-circle p-2 me-2"> </span>
                    <small class="fw-bold text-dark">امتحاناتي (مواد أخرى)</small>
                </div>
                
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary rounded-circle p-2 me-2"> </span>
                    <small class="fw-bold text-dark">امتحانات أساتذة آخرين</small>
                </div>
            </div>
            
            <div id='calendar'></div>
        </div>
    </div>
</div>

{{-- نافذة إضافة امتحان (Create Modal) --}}
<div class="modal fade" id="examModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">📌 تحديد موعد امتحان</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="examForm">
                    @csrf
                    <input type="hidden" id="selectedDate" name="exam_date">
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <input type="hidden" name="section_id" value="{{ $section->id }}">

                    <div class="mb-3">
                        <label class="form-label text-muted small">تاريخ الامتحان</label>
                        <input type="text" id="displayDate" class="form-control bg-light border-0 fw-bold" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان الامتحان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control border-primary" placeholder="مثلاً: اختبار منتصف الفصل" required>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">حفظ الموعد</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- نافذة التعديل والحذف (Edit/Delete Modal) --}}
<div class="modal fade" id="editExamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">✏️ تعديل موعد الامتحان</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editExamForm">
                    @csrf
                    <input type="hidden" id="editExamId" name="exam_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الامتحان</label>
                        <input type="text" id="editExamTitle" name="title" class="form-control border-warning" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ الامتحان</label>
                        <input type="date" id="editExamDate" name="exam_date" class="form-control border-warning" required>
                    </div>

                    <div class="d-flex justify-content-between gap-2 mt-4">
                        <button type="submit" class="btn btn-primary flex-grow-1 fw-bold">حفظ التغييرات</button>
                        <button type="button" id="deleteExamBtn" class="btn btn-danger flex-grow-1 fw-bold">
                            <i class="fas fa-trash-alt me-1"></i> حذف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var createModal = new bootstrap.Modal(document.getElementById('examModal'));
        var editModal = new bootstrap.Modal(document.getElementById('editExamModal'));

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            direction: 'rtl', 
            locale: 'ar',

            //   يخلوا الضغطة المطولة ربع ثانية بس بدلاً من ثانية كاملة
            eventLongPressDelay: 250, 
            selectLongPressDelay: 250,
            headerToolbar: { 
                left: 'prev,next today', 
                center: 'title', 
                right: 'dayGridMonth,listMonth' 
            },
            selectable: true,
            height: 'auto',
            events: "{{ route('teacher.schedule.events', ['subject_id' => $subject->id, 'section_id' => $section->id]) }}",

            dateClick: function(info) {
                document.getElementById('selectedDate').value = info.dateStr;
                document.getElementById('displayDate').value = info.dateStr;
                createModal.show();
            },

            // عند النقر على الموعد
            eventClick: function(info) {
                var props = info.event.extendedProps;
                
                if (props.canEdit) {
                    document.getElementById('editExamId').value = info.event.id;
                    document.getElementById('editExamTitle').value = props.realTitle; 
                    // ✅ تعبئة التاريخ الحالي في النافذة
                    document.getElementById('editExamDate').value = info.event.startStr; 
                    
                    let modalTitle = document.querySelector('#editExamModal .modal-title');
                    modalTitle.innerHTML = '✏️ تعديل امتحان (' + props.subjectName + ')';
                    
                    editModal.show();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'امتحان محجوز!',
                        text: 'الطلاب لديهم امتحان مادة (' + props.subjectName + ') في هذا اليوم.',
                        confirmButtonText: 'حسناً',
                        confirmButtonColor: '#6c757d'
                    });
                }
            },

            // ✅ الإضافة الجديدة: عند سحب وإفلات الامتحان ليوم آخر (Drag & Drop)
            eventDrop: function(info) {
                let formData = new FormData();
                formData.append('exam_id', info.event.id);
                formData.append('exam_date', info.event.startStr); // التاريخ الجديد الذي تم الإفلات فيه

                fetch("{{ route('teacher.schedule.update') }}", {
                    method: "POST", 
                    body: formData, 
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم النقل!',
                            text: 'تم تغيير موعد الامتحان بنجاح.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        // ❌ في حال وجود امتحان آخر في هذا اليوم، نرجع الموعد لمكانه الأصلي
                        info.revert(); 
                        Swal.fire({
                            icon: 'error',
                            title: 'تضارب مواعيد!',
                            text: data.message,
                            confirmButtonText: 'حسناً'
                        });
                    }
                });
            }
        });

        calendar.render();

        // إرسال بيانات الإضافة
        document.getElementById('examForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch("{{ route('teacher.schedule.store') }}", {
                method: "POST", 
                body: new FormData(this), 
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    createModal.hide();
                    this.reset();
                    calendar.refetchEvents();
                    // 2. تنبيه نجاح الإضافة
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الحفظ!',
                        text: 'تم تحديد موعد الامتحان بنجاح.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    // 3. تنبيه فشل الإضافة (تضارب مواعيد)
                    Swal.fire({
                        icon: 'error',
                        title: 'عذراً!',
                        text: data.message,
                        confirmButtonText: 'حسناً',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        // إرسال بيانات التعديل
        document.getElementById('editExamForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch("{{ route('teacher.schedule.update') }}", {
                method: "POST", 
                body: new FormData(this), 
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) { 
                    editModal.hide(); 
                    calendar.refetchEvents();
                    // 4. تنبيه نجاح التعديل
                    Swal.fire({
                        icon: 'success',
                        title: 'تم التعديل!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        // إرسال طلب الحذف (مع رسالة تأكيد احترافية)
        document.getElementById('deleteExamBtn').addEventListener('click', function() {
            // 5. نافذة تأكيد الحذف
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن حذف هذا الموعد!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف الموعد!',
                cancelButtonText: 'تراجع'
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = new FormData();
                    formData.append('exam_id', document.getElementById('editExamId').value);

                    fetch("{{ route('teacher.schedule.delete') }}", {
                        method: "POST", 
                        body: formData, 
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    })
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) { 
                            editModal.hide(); 
                            calendar.refetchEvents();
                            // 6. تنبيه نجاح الحذف
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الحذف!',
                                text: 'تم إلغاء موعد الامتحان بنجاح.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                }
            });
        });
    });
</script>

<style>
    .fc-toolbar-title { font-size: 1.4rem !important; font-weight: 800; color: #2c3e50; }
    .fc-event { border: none !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 2px 5px; }
    .fc-day-today { background-color: #f8faff !important; }
    .modal-header { border: none; }
    .form-control:focus { box-shadow: none; border-color: #3788d8; }
</style>

@endsection