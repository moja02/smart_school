@extends('layouts.admin')

@section('title', 'ترحيل الطلاب لنهاية السنة الدراسية')

@section('content')

{{-- 1. الترويسة الرئيسية --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">ترحيل نهاية السنة 🎓</h2>
            <p class="mb-0 opacity-75">
                من هنا يمكنك إدارة السنة الدراسية وترحيل الطلاب الناجحين للصف التالي وأرشفة نتائج الجميع.
            </p>
            <div class="mt-3">
                <span class="badge bg-light text-dark shadow-sm px-3 py-2 fs-6">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> السنة الحالية: {{ $school->academic_year ?? 'غير محددة' }}
                </span>
            </div>
        </div>
        <div class="d-none d-md-block">
            <i class="fas fa-graduation-cap fa-4x opacity-25 text-white"></i>
        </div>
    </div>
</div>

{{-- 2. بطاقة إدارة السنة الدراسية --}}
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-calendar-alt me-2"></i> إدارة السنة الدراسية</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.school.update_academic_year') }}" method="POST" id="academicYearForm">
            @csrf
            <div class="row align-items-end g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-dark">إضافة / تعديل السنة الدراسية</label>
                    <input type="text" name="academic_year" class="form-control" placeholder="مثال: 2026-2027" value="{{ $school->academic_year }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fas fa-save me-2"></i> حفظ السنة الدراسية
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 3. بطاقة الترحيل --}}
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom-0">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-exchange-alt me-2"></i> ترحيل الطلاب</h6>
    </div>
    
    <div class="card-body">
        {{-- أدوات التصفية --}}
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark">اختر الصف المراد ترحيله:</label>
                <select id="gradeSelect" class="form-select">
                    <option value="">-- اختر الصف --</option>
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }} (ترتيب: {{ $grade->order }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button id="btnPreview" class="btn btn-dark w-100">
                    <i class="fas fa-search me-2"></i> معاينة النتائج وحالة الترحيل
                </button>
            </div>
        </div>

        {{-- منطقة عرض حالة اكتمال التقييمات --}}
        <div id="assessmentCheckArea" style="display: none;">
            {{-- ستُملأ عبر JavaScript --}}
        </div>

        {{-- منطقة عرض النتائج --}}
        <div id="previewArea" style="display: none;">
            <div class="alert alert-secondary border-0 d-flex align-items-center">
                <i class="fas fa-info-circle me-3 fs-4"></i>
                <div>
                    <strong>ملاحظة هامة:</strong> الناجحون (نسبة ≥ 50%) سينتقلون تلقائياً للصف التالي، بينما سيبقى الراسبون في فصلهم الحالي. ستُحفظ نسخة مؤرشفة من جميع الدرجات.
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center" id="promotionTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم الطالب</th>
                            <th>الفصل الحالي</th>
                            <th>المجموع</th>
                            <th>النسبة</th>
                            <th>الحالة (آلي)</th>
                            <th>تعديل الحالة</th>
                            <th>الفصل الجديد (المتوقع)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- البيانات تُضاف عبر JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="text-start mt-4">
                <button id="btnExecute" class="btn btn-dark btn-lg px-5">
                    <i class="fas fa-check-double me-2"></i> تنفيذ الترحيل والأرشفة
                </button>
            </div>
        </div>

        {{-- رسالة التحميل --}}
        <div id="loadingArea" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-dark" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <h5 class="mt-3 text-muted">جاري تحليل بيانات الطلاب...</h5>
        </div>
    </div>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); }
    .bg-dark { background-color: #212529 !important; }
    .incomplete-table th { font-size: 0.85rem; }
    .incomplete-table td { font-size: 0.85rem; }
    .assessment-progress { 
        height: 8px; 
        border-radius: 4px; 
        background: #e9ecef; 
        overflow: hidden; 
    }
    .assessment-progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
</style>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let studentsData = [];
    let assessmentsComplete = false; // متغير لتتبع حالة اكتمال التقييمات

    // تأكيد حفظ السنة الدراسية
    document.getElementById('academicYearForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'سيتم تغيير السنة الدراسية الحالية للمدرسة.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#212529',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، حفظ',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // دالة التحقق من اكتمال التقييمات
    function checkAssessments(gradeId) {
        return fetch('{{ route("admin.promotion.check_assessments") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ grade_id: gradeId })
        })
        .then(response => response.json());
    }

    // عرض نتيجة فحص التقييمات
    function renderAssessmentCheck(data) {
        let area = document.getElementById('assessmentCheckArea');

        if (data.complete) {
            assessmentsComplete = true;
            area.innerHTML = `
                <div class="alert alert-success border-0 d-flex align-items-center mb-4 shadow-sm">
                    <i class="fas fa-check-circle me-3 fs-3 text-success"></i>
                    <div>
                        <strong class="d-block mb-1">✅ جميع التقييمات مكتملة</strong>
                        <span class="text-muted">جميع المواد في جميع الشعب تم إضافة تقييمات بالدرجات الكاملة المطلوبة. يمكنك المتابعة بالترحيل.</span>
                    </div>
                </div>
            `;
        } else {
            assessmentsComplete = false;
            let tableRows = data.incomplete.map((item, idx) => {
                let sem1Pct = item.max_per_semester > 0 ? (item.sum_sem1 / item.max_per_semester) * 100 : 0;
                let sem2Pct = item.max_per_semester > 0 ? (item.sum_sem2 / item.max_per_semester) * 100 : 0;
                let sem1Color = sem1Pct >= 100 ? '#198754' : (sem1Pct > 0 ? '#ffc107' : '#dc3545');
                let sem2Color = sem2Pct >= 100 ? '#198754' : (sem2Pct > 0 ? '#ffc107' : '#dc3545');

                return `
                    <tr>
                        <td>${idx + 1}</td>
                        <td class="fw-bold">${item.subject_name}</td>
                        <td>${item.section_name}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="assessment-progress flex-grow-1">
                                    <div class="assessment-progress-bar" style="width: ${Math.min(sem1Pct, 100)}%; background: ${sem1Color};"></div>
                                </div>
                                <small class="text-nowrap">${item.sum_sem1} / ${item.max_per_semester}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="assessment-progress flex-grow-1">
                                    <div class="assessment-progress-bar" style="width: ${Math.min(sem2Pct, 100)}%; background: ${sem2Color};"></div>
                                </div>
                                <small class="text-nowrap">${item.sum_sem2} / ${item.max_per_semester}</small>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">${item.total_works_score}</span></td>
                    </tr>
                `;
            }).join('');

            area.innerHTML = `
                <div class="alert alert-danger border-0 mb-4 shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exclamation-triangle me-3 fs-3 text-danger"></i>
                        <div>
                            <strong class="d-block mb-1">⚠️ لا يمكن إتمام الترحيل - تقييمات غير مكتملة</strong>
                            <span>يجب أن يتم إضافة تقييمات بنفس قيمة إجمالي درجات أعمال السنة المحددة لكل مادة في كل شعبة قبل إنهاء السنة الدراسية.</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover bg-white rounded overflow-hidden incomplete-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المادة</th>
                                    <th>الشعبة</th>
                                    <th>الفصل الأول (تقييمات / مطلوب)</th>
                                    <th>الفصل الثاني (تقييمات / مطلوب)</th>
                                    <th>إجمالي درجة الأعمال</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        area.style.display = 'block';
    }

    // جلب المعاينة (مع التحقق من التقييمات أولاً)
    document.getElementById('btnPreview').addEventListener('click', function() {
        let gradeId = document.getElementById('gradeSelect').value;
        if (!gradeId) {
            Swal.fire('تنبيه', 'يرجى اختيار الصف أولاً', 'warning');
            return;
        }

        document.getElementById('previewArea').style.display = 'none';
        document.getElementById('assessmentCheckArea').style.display = 'none';
        document.getElementById('loadingArea').style.display = 'block';

        // الخطوة 1: التحقق من اكتمال التقييمات
        checkAssessments(gradeId)
        .then(checkData => {
            if (checkData.success) {
                renderAssessmentCheck(checkData);
            }

            // الخطوة 2: جلب بيانات الطلاب (بغض النظر عن اكتمال التقييمات، لعرض المعاينة)
            return fetch('{{ route("admin.promotion.preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ grade_id: gradeId, pass_percentage: 50 })
            });
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingArea').style.display = 'none';
            if (data.success) {
                studentsData = data.students;
                renderTable();
                document.getElementById('previewArea').style.display = 'block';

                // إخفاء/إظهار زر التنفيذ بناءً على اكتمال التقييمات
                let btnExecute = document.getElementById('btnExecute');
                if (!assessmentsComplete) {
                    btnExecute.disabled = true;
                    btnExecute.classList.remove('btn-dark');
                    btnExecute.classList.add('btn-secondary');
                    btnExecute.innerHTML = '<i class="fas fa-ban me-2"></i> لا يمكن الترحيل - أكمل التقييمات أولاً';
                } else {
                    btnExecute.disabled = false;
                    btnExecute.classList.remove('btn-secondary');
                    btnExecute.classList.add('btn-dark');
                    btnExecute.innerHTML = '<i class="fas fa-check-double me-2"></i> تنفيذ الترحيل والأرشفة';
                }
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        })
        .catch(error => {
            document.getElementById('loadingArea').style.display = 'none';
            Swal.fire('خطأ', 'حدث خطأ في الاتصال بالسيرفر.', 'error');
        });
    });

    // رسم الجدول
    function renderTable() {
        let tbody = document.querySelector('#promotionTable tbody');
        tbody.innerHTML = '';
        
        if(studentsData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-muted py-4">لا يوجد طلاب في هذا الصف.</td></tr>';
            return;
        }

        studentsData.forEach((student, index) => {
            let statusBadge = student.status === 'passed' 
                ? '<span class="badge bg-success">ناجح ✅</span>' 
                : '<span class="badge bg-danger">راسب ❌</span>';

            let nextClassHtml = student.new_class_name;
            if (student.is_graduate) {
                nextClassHtml = '<span class="badge bg-dark"><i class="fas fa-graduation-cap"></i> متخرج</span>';
            } else if (student.status === 'failed') {
                nextClassHtml = '<span class="text-muted">يبقى في: ' + student.current_class + '</span>';
            }

            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td class="fw-bold">${student.name}</td>
                <td>${student.current_class}</td>
                <td>${student.total_score} / ${student.max_possible}</td>
                <td><span class="fw-bold">${student.percentage}%</span></td>
                <td id="status_badge_${index}">${statusBadge}</td>
                <td>
                    <select class="form-select form-select-sm status-toggle" data-index="${index}">
                        <option value="passed" ${student.status === 'passed' ? 'selected' : ''}>ناجح</option>
                        <option value="failed" ${student.status === 'failed' ? 'selected' : ''}>راسب</option>
                    </select>
                </td>
                <td id="next_class_${index}">${nextClassHtml}</td>
            `;
            tbody.appendChild(tr);
        });

        // تفعيل تغيير الحالة اليدوي
        document.querySelectorAll('.status-toggle').forEach(select => {
            select.addEventListener('change', function() {
                let idx = this.getAttribute('data-index');
                let newStatus = this.value;
                studentsData[idx].status = newStatus;
                
                let badge = document.getElementById('status_badge_' + idx);
                let nextCol = document.getElementById('next_class_' + idx);
                
                if (newStatus === 'passed') {
                    badge.innerHTML = '<span class="badge bg-success">ناجح ✅</span>';
                    if(studentsData[idx].is_graduate) {
                        nextCol.innerHTML = '<span class="badge bg-dark"><i class="fas fa-graduation-cap"></i> متخرج</span>';
                    } else {
                        nextCol.innerHTML = studentsData[idx].new_class_name;
                    }
                } else {
                    badge.innerHTML = '<span class="badge bg-danger">راسب ❌</span>';
                    nextCol.innerHTML = '<span class="text-muted">يبقى في: ' + studentsData[idx].current_class + '</span>';
                }
            });
        });
    }

    // تنفيذ الترحيل
    document.getElementById('btnExecute').addEventListener('click', function() {
        // حماية إضافية: منع الترحيل إذا لم تكتمل التقييمات
        if (!assessmentsComplete) {
            Swal.fire('غير مسموح', 'يجب إكمال جميع التقييمات قبل تنفيذ الترحيل.', 'error');
            return;
        }

        Swal.fire({
            title: 'تحذير هام!',
            text: 'هل أنت متأكد من تنفيذ الترحيل؟ سيتم نقل الناجحين وأرشفة بيانات الجميع بشكل نهائي.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#212529',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، نفّذ الترحيل!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: 'جاري التنفيذ...',
                    text: 'يرجى الانتظار وعدم إغلاق الصفحة.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('{{ route("admin.promotion.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ students: studentsData })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم بنجاح!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('خطأ', 'فشل في الاتصال بالسيرفر.', 'error');
                });
            }
        });
    });
</script>
@endsection

