@extends('layouts.teacher')

@section('content')

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">📅 تقويم الامتحانات: {{ $class->name }}</h5>
            <a href="{{ route('teacher.class', $class->id) }}" class="btn btn-light btn-sm rounded-pill text-primary fw-bold">
                <i class="fas fa-arrow-right me-1"></i> عودة للفصل
            </a>
        </div>
        <div class="card-body">
            
            {{-- مفتاح الألوان والتنبيهات --}}
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="alert alert-info d-flex align-items-center mb-0 py-2 px-3">
                    <i class="fas fa-info-circle fs-5 me-2"></i>
                    <div>اضغط على أي يوم فارغ لتحديد امتحان لمادة <strong>{{ $subject->name }}</strong>.</div>
                </div>

                <div class="d-flex gap-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary rounded-circle p-2 me-2"> </span>
                        <small class="fw-bold">امتحاناتي ({{ $subject->name }})</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary rounded-circle p-2 me-2"> </span>
                        <small class="fw-bold">امتحانات مواد أخرى</small>
                    </div>
                </div>
            </div>
            
            <div id='calendar'></div>
        </div>
    </div>
</div>

{{-- نافذة إضافة امتحان --}}
<div class="modal fade" id="examModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">📌 تحديد موعد امتحان جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="examForm">
                    @csrf
                    <input type="hidden" id="selectedDate" name="exam_date">
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <input type="hidden" name="class_id" value="{{ $class->id }}">

                    <div class="mb-3">
                        <label class="form-label text-muted">التاريخ المختار</label>
                        <input type="text" id="displayDate" class="form-control bg-light fw-bold" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان الامتحان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="مثلاً: امتحان الشهر الأول" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">حفظ الموعد</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- نافذة تعديل وحذف الامتحان --}}
<div class="modal fade" id="editExamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">✏️ تعديل الامتحان</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editExamForm">
                    @csrf
                    <input type="hidden" id="editExamId" name="exam_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان الامتحان</label>
                        <input type="text" id="editExamTitle" name="title" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary fw-bold">حفظ التعديلات</button>
                        <button type="button" id="deleteExamBtn" class="btn btn-danger">🗑 حذف الامتحان</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        // نوافذ Modal
        var createModal = new bootstrap.Modal(document.getElementById('examModal'));
        var editModal = new bootstrap.Modal(document.getElementById('editExamModal'));

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            direction: 'rtl', locale: 'ar',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
            selectable: true, height: 'auto',
            events: "{{ route('teacher.schedule.events', ['subject_id' => $subject->id, 'class_id' => $class->id]) }}",

            // 1. عند النقر على يوم فارغ -> إضافة
            dateClick: function(info) {
                document.getElementById('selectedDate').value = info.dateStr;
                document.getElementById('displayDate').value = info.dateStr;
                createModal.show();
            },

            // 2. عند النقر على امتحان موجود -> تعديل أو حذف
            eventClick: function(info) {
    var props = info.event.extendedProps;
    
    // فحص هل يمكن التعديل (نفس المادة ونفس المعلم)
    if (props.canEdit) {
        // نعم، هذا امتحان هذه المادة -> افتح نافذة التعديل
        document.getElementById('editExamId').value = info.event.id;
        document.getElementById('editExamTitle').value = info.event.title;
        editModal.show();
    } else {
        // لا، هذا امتحان مادة أخرى (حتى لو كان لي)
        alert(
            '⚠️ تنبيه:\n' +
            'هذا امتحان لمادة: ' + props.subjectName + '\n' +
            'بعنوان: ' + info.event.title + '\n\n' +
            'لا يمكنك تعديله من هنا. يرجى الذهاب لصفحة تلك المادة لتعديله.'
        );
    }
    }
});

        calendar.render();

        // --- كود الحفظ الجديد (Create) ---
        document.getElementById('examForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch("{{ route('teacher.schedule.store') }}", {
                method: "POST", body: formData, headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(r => r.json()).then(data => {
                if(data.success) { createModal.hide(); this.reset(); calendar.refetchEvents(); alert('✅ تم الحفظ'); }
            });
        });

        // --- كود التعديل (Update) ---
        document.getElementById('editExamForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch("{{ route('teacher.schedule.update') }}", {
                method: "POST", body: formData, headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(r => r.json()).then(data => {
                if(data.success) { editModal.hide(); calendar.refetchEvents(); alert('✅ تم التعديل'); }
            });
        });

        // --- كود الحذف (Delete) ---
        document.getElementById('deleteExamBtn').addEventListener('click', function() {
            if(!confirm('هل أنت متأكد من حذف هذا الامتحان؟')) return;
            
            let examId = document.getElementById('editExamId').value;
            let formData = new FormData();
            formData.append('exam_id', examId);

            fetch("{{ route('teacher.schedule.delete') }}", {
                method: "POST", body: formData, headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(r => r.json()).then(data => {
                if(data.success) { editModal.hide(); calendar.refetchEvents(); alert('🗑 تم الحذف'); }
            });
        });
    });
</script>

<style>
    .fc-toolbar-title { font-size: 1.5rem !important; font-weight: bold; }
    .fc-event { cursor: pointer; border-radius: 4px; padding: 3px; font-size: 0.85rem; }
    .fc-daygrid-day.fc-day-today { background-color: #e8f4ff !important; }
</style>

@endsection