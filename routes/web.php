<?php

use Illuminate\Support\Facades\Route;

// استدعاء الكنترولرز
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\userController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ScheduleController;

// ====================================================
// 1. المصادقة والصفحة الرئيسية
// ====================================================

Route::get('/', function () {
    return redirect()->route('login.form');
});

// تسجيل الدخول والخروج
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login',   [AuthController::class, 'login'])->name('login');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// تسجيل مستخدم جديد (إن وجد)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// الصفحة العامة بعد الدخول
Route::get('/home', [userController::class, 'index'])->name('home');


// ====================================================
// 2. راوتات محمية (تتطلب تسجيل دخول)
// ====================================================
Route::middleware(['auth'])->group(function () {
    // روابط الملف الشخصي الموحدة
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    // ==========================================
    // 👔 روابط مدير المدرسة (School Manager)
    // ==========================================
    Route::middleware(['is_manager'])->group(function () {
        
    // 1. لوحة تحكم المدير
    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');

    // 2. صلاحية تعيين مسؤول الدراسة
    Route::get('/manager/create-admin', [ManagerController::class, 'createStudyOfficer'])->name('manager.create_admin');
    Route::post('/manager/store-admin', [AdminController::class, 'storeStudyOfficer'])->name('manager.store_admin');

    // 3. صفحات العرض
    Route::get('/manager/teachers', [ManagerController::class, 'listTeachers'])->name('manager.teachers.index');

    // سجل مراقبة النظام (System Logs)
    Route::get('/manager/system-logs', [ManagerController::class, 'systemLogs'])->name('manager.system_logs');
    
    });
    // التوجيه العام للوحة التحكم
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ====================================================
    // A. لوحة تحكم الأدمن (Admin Dashboard & Management)
    // ====================================================
    Route::middleware(['role:admin'])->group(function () {
        
        Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        // مسارات إعدادات هيكلية المدرسة (الجديدة)
        Route::get('/settings/structure', [AdminController::class, 'editSchoolStructure'])->name('admin.settings.structure');
        Route::post('/settings/structure', [AdminController::class, 'updateSchoolStructure'])->name('admin.settings.structure.update');
        // 1. إدارة المستخدمين (Users)
        Route::get('/admin/users', [AdminController::class, 'listUsers'])->name('admin.users'); // تم تحديث الاسم ليتوافق مع الفلترة
        Route::get('/admin/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
        Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
        // (إضافات اختيارية لتعديل المستخدم وتصفير الباسورد)
        Route::get('/admin/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
        Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/admin/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('admin.users.resetPassword');

        // 2. إدارة الهيكل الدراسي (Grades & Classes)
        // أ. السنوات الدراسية (Grades) - ✅ المسار الجديد المهم
        Route::post('/admin/grades', [AdminController::class, 'storeGrade'])->name('admin.grades.store');

        // ب. الفصول (Classes)
        Route::get('/admin/classes', [AdminController::class, 'listClasses'])->name('admin.classes');
        Route::post('/admin/classes', [AdminController::class, 'storeClass'])->name('admin.classes.store');
        Route::delete('/admin/classes/{id}', [AdminController::class, 'deleteClass'])->name('admin.classes.delete');
        Route::get('/admin/classes/{id}/edit', [AdminController::class, 'editClass'])->name('admin.classes.edit');
        Route::put('/admin/classes/{id}', [AdminController::class, 'updateClass'])->name('admin.classes.update');
        Route::get('/admin/classes/create', [AdminController::class, 'createClass'])->name('admin.classes.create'); // صفحة النموذج
        Route::post('/admin/classes', [AdminController::class, 'storeClass'])->name('admin.classes.store'); // حفظ البيانات
        Route::post('/admin/grades', [AdminController::class, 'storeGrade'])->name('admin.grades.store');
        
        // رابط نقل الطلاب الجماعي
        Route::post('/students/bulk-transfer', [AdminController::class, 'bulkTransfer'])->name('admin.students.bulk_transfer');
        // عرض طلاب فصل معين (تقرير)
        Route::get('/admin/classes/{id}/students', [AdminController::class, 'showClassStudents'])->name('admin.classes.students');
        // 1. تقرير السنة الدراسية كاملة (مثلاً: كل طلاب الصف السادس)
        Route::get('/admin/grades/{id}/report', [AdminController::class, 'showGradeReport'])->name('admin.grades.report');
        // 3. إدارة المواد (Subjects)
        // صفحة عرض وتوزيع الدرجات
        Route::get('/subjects/grades-distribution', [AdminController::class, 'gradeSettings'])->name('admin.subjects.grade_settings');
        // حفظ التوزيع في قاعدة البيانات
        Route::post('/subjects/grades-distribution', [AdminController::class, 'storeGradeSettings'])->name('admin.subjects.store_grade_settings');
        Route::put('/subjects/update', [AdminController::class, 'updateSubject'])->name('admin.subjects.update'); // ✅ مسار التعديل
        Route::post('/subjects/update-classes', [AdminController::class, 'updateSubjectClasses'])->name('admin.subjects.update_classes');
        Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('admin.subjects.store');
        Route::get('/admin/subjects', [AdminController::class, 'listSubjects'])->name('admin.subjects');
        Route::post('/admin/subject', [AdminController::class, 'storeSubject'])->name('admin.storeSubject'); // اسم الراوت كما هو في الفورم
        Route::delete('/admin/subjects/{id}', [AdminController::class, 'deleteSubject'])->name('admin.subjects.delete');
        // (تعديل المواد)
        Route::get('/admin/subjects/{id}/edit', [AdminController::class, 'editSubject'])->name('admin.subjects.edit');
        Route::put('/admin/subjects/{id}', [AdminController::class, 'updateSubject'])->name('admin.subjects.update');

        // 4. توزيع المواد على المعلمين (Assignment)
        // مسار حفظ إسناد المواد للمعلمين
        Route::post('/admin/assign/store', [AdminController::class, 'storeAssignment'])->name('admin.assign.store');
        // مسار إلغاء إسناد مادة لمدرس من شعبة معينة
        Route::delete('/admin/assign/remove/{section_id}', [AdminController::class, 'removeAssignment'])->name('admin.assign.remove');
        // مسار تحديث أستاذ المادة لشعبة معينة
Route::put('/admin/assign/update', [AdminController::class, 'updateAssignment'])->name('admin.assign.update');
        Route::get('/admin/assign', [AdminController::class, 'createAssignment'])->name('admin.assign');
        Route::post('/admin/assign', [AdminController::class, 'storeAssignment'])->name('admin.storeAssign');
        // ✅ مسار AJAX لجلب المواد المتاحة حسب الفصل (مهم جداً للتوزيع)
        Route::get('/admin/assign/ajax/{class_id}', [AdminController::class, 'getAvailableSubjects'])->name('admin.assign.getSubjects');

        // 5. إدارة الطلاب (Students Management)
        Route::get('/students', [AdminController::class, 'listStudents'])->name('admin.students');
        Route::delete('/admin/students/{id}', [AdminController::class, 'deleteStudent'])->name('admin.students.delete');
        
        // الطلاب غير الموزعين وتسكينهم
        Route::get('/admin/students/unassigned', [AdminController::class, 'listUnassignedStudents'])->name('admin.students.unassigned');
        Route::post('/admin/students/update-class', [AdminController::class, 'updateStudentClass'])->name('admin.students.updateClass');

        // 6. ربط الآباء
        Route::get('/admin/parents/link', [AdminController::class, 'createParentLink'])->name('admin.parents.link');
        Route::post('/admin/parents/link', [AdminController::class, 'storeParentLink'])->name('admin.parents.storeLink');
        Route::delete('/admin/parents/link/{id}', [AdminController::class, 'deleteParentLink'])->name('admin.parents.deleteLink');

        // 7. الجداول الدراسية
        Route::get('/admin/schedule', [AdminController::class, 'listSchedules'])->name('admin.schedule.index');

        // 8. التقارير
        Route::get('/admin/reports', [AdminController::class, 'showReports'])->name('admin.reports.index');
        // مسار طباعة تقرير الأوائل (صفحة مستقلة)
        Route::get('/admin/reports/print', [AdminController::class, 'printReport'])->name('admin.reports.print');
        Route::get('/admin/reports/certificate/{student_id}', [AdminController::class, 'printCertificate'])->name('admin.reports.print_certificate');

        //9. تشغيل او ايقاف الرصد
        Route::post('/grading/toggle', [AdminController::class, 'toggleGrading'])->name('admin.grading.toggle');
    });

    // ====================================================
    // B. لوحة تحكم المعلم (Teacher Dashboard)
    // ====================================================
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        // الرئيسية والملف
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
        Route::get('/profile', [TeacherController::class, 'profile'])->name('teacher.profile');
        
        // الفصول والطلاب
        Route::get('/classes', [TeacherController::class, 'myClasses'])->name('teacher.classes');
        Route::get('/class/{id}', [TeacherController::class, 'showClass'])->name('teacher.class');
        Route::get('/students', [TeacherController::class, 'students'])->name('teacher.students'); // قد يكون مكرر مع showClass

        // إدارة المادة التعليمية
        Route::get('/subject/{subject_id}/class/{class_id}', [TeacherController::class, 'showSubject'])->name('teacher.subject.show');
        Route::get('/teacher/class/{subject_id}/{class_id}', [TeacherController::class, 'showClass'])->name('teacher.class.show');

        // الأسئلة والتقييمات
        Route::get('/subject/{subject_id}/class/{class_id}/questions/create', [TeacherController::class, 'createQuestion'])->name('teacher.questions.create');
        Route::post('/subject/{subject_id}/class/{class_id}/questions', [TeacherController::class, 'storeQuestion'])->name('teacher.questions.store');
        // صفحة رصد درجات تقييم معين
        Route::get('/teacher/assessments/{assessment_id}/marks', [AssessmentController::class, 'editMarks'])->name('teacher.assessments.marks');
        
        // صفحة عرض التقييمات الحالية للفصل
        Route::get('/teacher/assessments/{subject_id}/{section_id}', [AssessmentController::class, 'index'])->name('teacher.assessments.index');

        // حفظ تقييم جديد (مع التحقق من الحد الأقصى)
        Route::post('/teacher/assessments/store', [AssessmentController::class, 'store'])->name('teacher.assessments.store');


        // حفظ درجات الطلاب وتحديث المجموع الكلي
        Route::post('/teacher/assessments/save-marks', [AssessmentController::class, 'saveMarks'])->name('teacher.assessments.save_marks');
        // صفحة رصد الامتحان النهائي
        Route::get('/teacher/final-grades/{subject_id}/{section_id}', [TeacherController::class, 'editFinalGrades'])->name('teacher.final_grades.edit');

        // حفظ درجات النهائي
        Route::post('/teacher/final-grades/store', [TeacherController::class, 'storeFinalGrades'])->name('teacher.final_grades.store');
        
        // ✅ صفحة عرض كشف رصد الدرجات
        Route::get('/teacher/grades/{subject_id}/{section_id}', [TeacherController::class, 'createGrades'])
        ->name('teacher.grades.create');

        // ✅ رابط حفظ الدرجات عند الضغط على زر الحفظ
        Route::post('/teacher/grades/store', [TeacherController::class, 'storeGrades'])
        ->name('teacher.grades.store');

        // الغياب
        Route::get('/teacher/attendance/{section_id}', [AttendanceController::class, 'index'])->name('teacher.attendance.index');
        Route::post('/teacher/attendance/store', [AttendanceController::class, 'store'])->name('teacher.attendance.store');

        // إدارة الاختبارات التجريبية (Quizzes)
        Route::get('/subject/{subject_id}/class/{section_id}/quizzes', [TeacherController::class, 'indexQuizzes'])->name('teacher.quizzes.index');
        Route::get('/subject/{subject_id}/class/{section_id}/quizzes/create', [TeacherController::class, 'createQuiz'])->name('teacher.quizzes.create');
        Route::post('/teacher/quizzes/store', [TeacherController::class, 'storeQuiz'])->name('teacher.quizzes.store');
        Route::get('/teacher/quizzes/{id}/results', [TeacherController::class, 'quizResults'])->name('teacher.quizzes.results');
        Route::delete('/teacher/quizzes/{id}', [TeacherController::class, 'deleteQuiz'])->name('teacher.quizzes.delete');
        Route::delete('/teacher/questions/{id}', [TeacherController::class, 'destroyQuestion'])->name('teacher.questions.destroy');
        Route::get('/teacher/quizzes/{id}', [TeacherController::class, 'showQuiz'])->name('teacher.quizzes.show');
        Route::put('/teacher/questions/{id}', [TeacherController::class, 'updateQuestion'])->name('teacher.questions.update');
        Route::get('/teacher/quizzes/{id}/report', [TeacherController::class, 'quizReport'])->name('teacher.quizzes.report');
        Route::get('/teacher/quizzes/{id}/results', [App\Http\Controllers\TeacherController::class, 'showQuizResults'])->name('teacher.quizzes.results');
        Route::get('/teacher/quizzes/{id}/print-results', [App\Http\Controllers\TeacherController::class, 'printQuizResults'])->name('teacher.quizzes.print');
       
        // صفحة التقويم الرئيسية (تأخذ معرف المادة ومعرف الشعبة)
        Route::get('/teacher/schedule/{subject_id}/{section_id}', [App\Http\Controllers\ScheduleController::class, 'index'])
            ->name('teacher.schedule.index');

        Route::get('/teacher/schedule/events/{subject_id}/{section_id}', [App\Http\Controllers\ScheduleController::class, 'getEvents'])
            ->name('teacher.schedule.events');

        // رابط حفظ امتحان جديد
        Route::post('/teacher/schedule/store', [App\Http\Controllers\ScheduleController::class, 'store'])->name('teacher.schedule.store');

        // رابط تعديل امتحان موجود
        Route::post('/teacher/schedule/update', [App\Http\Controllers\ScheduleController::class, 'update'])->name('teacher.schedule.update');

        // رابط حذف امتحان
        Route::post('/teacher/schedule/delete', [App\Http\Controllers\ScheduleController::class, 'delete'])->name('teacher.schedule.delete');
        // التقارير
        Route::get('/subject/{subject_id}/class/{class_id}/report', [TeacherController::class, 'subjectReport'])->name('teacher.subject.report');
        Route::get('/subject/{subject_id}/class/{class_id}/report/print', [TeacherController::class, 'printReport'])->name('teacher.subject.report.print');
    });

    // ====================================================
    // C. لوحة تحكم الطالب (Student Dashboard)
    // ====================================================
    Route::middleware(['role:student'])->prefix('student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
        
        // المواد والدرجات
        Route::get('/student/report-card', [\App\Http\Controllers\StudentController::class, 'reportCard'])->name('student.report_card');
        Route::get('/my-subjects', [StudentController::class, 'mySubjects'])->name('student.subjects.index');
        Route::get('/subject/{id}', [StudentController::class, 'showSubject'])->name('student.subjects.show');
        Route::get('/my-grades', [StudentController::class, 'myGrades'])->name('student.grades'); // الاسم القديم كان student.grades

        // البروفايل والجدول
        Route::get('/profile', [StudentController::class, 'profile'])->name('student.profile');
        Route::post('/profile', [StudentController::class, 'updateProfile'])->name('student.updateProfile');
        Route::get('/schedule', [StudentController::class, 'schedule'])->name('student.schedule');
        Route::get('/attendance', [StudentController::class, 'attendance'])->name('student.attendance');
        Route::get('/messages', [StudentController::class, 'messages'])->name('student.messages');

        // الاختبارات (Quiz)
        Route::get('/lesson/{id}/quiz', [StudentController::class, 'startQuiz'])->name('student.quiz.start');
        Route::post('/lesson/{id}/quiz', [StudentController::class, 'submitQuiz'])->name('student.quiz.submit');
    });

    // ====================================================
    // D. لوحة تحكم ولي الأمر (Parent Dashboard)
    // ====================================================
    Route::middleware(['role:parent'])->prefix('parent')->group(function () {
        Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('parent.dashboard');
        
        Route::get('/profile', [ParentController::class, 'editProfile'])->name('parent.profile');
        Route::post('/profile', [ParentController::class, 'updateProfile'])->name('parent.updateProfile');
        
        Route::get('/children', [ParentController::class, 'children'])->name('parent.children');
        Route::get('/grades', [ParentController::class, 'grades'])->name('parent.grades');
        Route::get('/attendance', [ParentController::class, 'attendance'])->name('parent.attendance');
        Route::get('/behaviour', [ParentController::class, 'behaviour'])->name('parent.behaviour');
        Route::get('/messages', [ParentController::class, 'messages'])->name('parent.messages');
    });

    // ====================================================
    // E. المحادثات (Messages - عام لكل المستخدمين المسجلين)
    // ====================================================
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{userId}', [MessageController::class, 'chat'])->name('messages.chat');
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->name('messages.send');

});