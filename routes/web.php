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

    // ==========================================
    // 👔 روابط مدير المدرسة (School Manager)
    // ==========================================
    Route::middleware(['is_manager'])->prefix('manager')->name('manager.')->group(function () {
        
        // 1. لوحة تحكم المدير
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');

        // 2. صلاحية تعيين مسؤول الدراسة
        Route::get('/create-admin', [AdminController::class, 'createStudyOfficer'])->name('create_admin');
        Route::post('/store-admin', [AdminController::class, 'storeStudyOfficer'])->name('store_admin');

        // 3. صفحات العرض
        Route::get('/teachers', [ManagerController::class, 'listTeachers'])->name('teachers.index');
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
        // ✅ هذا هو السطر الذي تم إصلاحه هنا (تغيير listSchedules إلى showSchedules)
        Route::get('/admin/schedule', [AdminController::class, 'showSchedules'])->name('admin.schedule.index');

        // 8. التقارير
        Route::get('/admin/reports', [AdminController::class, 'showReports'])->name('admin.reports.index');
        // مسار طباعة تقرير الأوائل (صفحة مستقلة)
        Route::get('/admin/reports/print', [AdminController::class, 'printReport'])->name('admin.reports.print');
        Route::get('/admin/reports/certificate/{student_id}', [AdminController::class, 'printCertificate'])->name('admin.reports.print_certificate');
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

        // الامتحانات والتقويم
        Route::get('/subject/{subject_id}/class/{class_id}/schedule', [TeacherController::class, 'showSchedule'])->name('teacher.schedule.index');
        Route::post('/schedule/store', [TeacherController::class, 'storeExam'])->name('teacher.schedule.store');
        Route::get('/subject/{subject_id}/class/{class_id}/schedule/events', [TeacherController::class, 'getExamsEvents'])->name('teacher.schedule.events');
        Route::post('/schedule/update', [TeacherController::class, 'updateExam'])->name('teacher.schedule.update');
        Route::post('/schedule/delete', [TeacherController::class, 'deleteExam'])->name('teacher.schedule.delete');

        // الأسئلة والتقييمات
        Route::get('/subject/{subject_id}/class/{class_id}/questions/create', [TeacherController::class, 'createQuestion'])->name('teacher.questions.create');
        Route::post('/subject/{subject_id}/class/{class_id}/questions', [TeacherController::class, 'storeQuestion'])->name('teacher.questions.store');
        
        Route::get('/subject/{subject_id}/class/{class_id}/assessments', [TeacherController::class, 'createAssessment'])->name('teacher.assessments.index');
        Route::post('/subject/{subject_id}/class/{class_id}/assessments', [TeacherController::class, 'storeAssessment'])->name('teacher.assessments.store');

        // رصد الدرجات
        Route::get('/subject/{subject_id}/class/{class_id}/assessment/{assessment_id}', [TeacherController::class, 'monitorGrades'])->name('teacher.assessments.monitor');
        Route::post('/subject/{subject_id}/class/{class_id}/assessment/{assessment_id}', [TeacherController::class, 'storeGrades'])->name('teacher.assessments.store_grades');
        // (روابط قديمة للدرجات - يمكن الاحتفاظ بها للتوافق)
        Route::get('/grade/create/{student_id}', [TeacherController::class, 'createGrade'])->name('teacher.createGrade');
        Route::post('/grade/store/{student_id}', [TeacherController::class, 'storeGrade'])->name('teacher.storeGrade');

        // الغياب
        Route::get('/class/{id}/attendance', [TeacherController::class, 'attendance'])->name('teacher.attendance');
        Route::post('/class/{id}/attendance', [TeacherController::class, 'storeAttendance'])->name('teacher.attendance.store');

        // الدروس
        Route::post('/lesson/store', [TeacherController::class, 'storeLesson'])->name('teacher.lessons.store');
        Route::get('/lesson/{id}/edit', [TeacherController::class, 'editLesson'])->name('teacher.lesson.edit');
        Route::put('/lesson/{id}', [TeacherController::class, 'updateLesson'])->name('teacher.lesson.update');

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
    
    Route::prefix('schedules')->name('schedules.')->group(function() {
    // عرض الجداول
    Route::get('/view', [AdminController::class, 'showSchedules'])->name('view');
    
    // قائمة المعلمين للتفضيلات
    Route::get('/preferences', [AdminController::class, 'preferencesList'])->name('preferences');
    
    // تعديل وحفظ التفضيلات
    Route::get('/preferences/{id}/edit', [AdminController::class, 'editPreference'])->name('preferences.edit');
    Route::post('/preferences/{id}/store', [AdminController::class, 'storePreference'])->name('preferences.store');
});

// داخل مجموعة الـ admin
Route::prefix('schedules')->name('admin.schedules.')->group(function() {
    // عرض الجداول (index.blade.php)
    Route::get('/view', [AdminController::class, 'showSchedules'])->name('view');
    
    // قائمة المعلمين (Preferences) - سأجعلها توجه لنفس الصفحة أو صفحة القائمة
    Route::get('/preferences', [AdminController::class, 'preferencesList'])->name('preferences');
    
    // تعديل التفضيلات (edit_preference.blade.php)
    Route::get('/preferences/{id}/edit', [AdminController::class, 'editPreference'])->name('preferences.edit');
    
    // حفظ التفضيلات
    Route::post('/preferences/{id}/store', [AdminController::class, 'storePreference'])->name('preferences.store');
});

Route::post('/schedules/generate', [AdminController::class, 'generateAutoSchedule'])->name('admin.schedules.generate');

});