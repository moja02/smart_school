<?php
$routesFile = __DIR__ . '/routes/web.php';
$content = file_get_contents($routesFile);

// 1. Add namespace imports at the top
$imports = "use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;\n"
         . "use App\Http\Controllers\Admin\UserController as AdminUserController;\n"
         . "use App\Http\Controllers\Admin\SchoolStructureController;\n"
         . "use App\Http\Controllers\Admin\StudentController as AdminStudentController;\n"
         . "use App\Http\Controllers\Admin\ParentController as AdminParentController;\n"
         . "use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;\n"
         . "use App\Http\Controllers\Admin\ReportController as AdminReportController;\n"
         . "use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;\n"
         . "use App\Http\Controllers\Admin\OperationController as AdminOperationController;\n";

$content = str_replace("use App\Http\Controllers\AdminController;", "use App\Http\Controllers\AdminController;\n" . $imports, $content);

// 2. Map routes
$replacements = [
    "\[AdminController::class, 'storeStudyOfficer'\]" => "[AdminUserController::class, 'storeStudyOfficer']",
    "\[App\\\\Http\\\\Controllers\\\\AdminController::class, 'manageAdminsPermissions'\]" => "[AdminOperationController::class, 'manageAdminsPermissions']",
    "\[App\\\\Http\\\\Controllers\\\\AdminController::class, 'updateAdminsPermissions'\]" => "[AdminOperationController::class, 'updateAdminsPermissions']",
    "\[AdminController::class, 'toggleBan'\]" => "[AdminUserController::class, 'toggleBan']",
    "\[AdminController::class, 'index'\]" => "[AdminDashboardController::class, 'index']",
    "\[AdminController::class, 'editSchoolStructure'\]" => "[SchoolStructureController::class, 'editSchoolStructure']",
    "\[AdminController::class, 'updateSchoolStructure'\]" => "[SchoolStructureController::class, 'updateSchoolStructure']",
    "\[AdminController::class, 'listUsers'\]" => "[AdminUserController::class, 'listUsers']",
    "\[AdminController::class, 'createUser'\]" => "[AdminUserController::class, 'createUser']",
    "\[AdminController::class, 'storeUser'\]" => "[AdminUserController::class, 'storeUser']",
    "\[AdminController::class, 'deleteUser'\]" => "[AdminUserController::class, 'deleteUser']",
    "\[AdminController::class, 'editUser'\]" => "[AdminUserController::class, 'editUser']",
    "\[AdminController::class, 'updateUser'\]" => "[AdminUserController::class, 'updateUser']",
    "\[AdminController::class, 'resetPassword'\]" => "[AdminUserController::class, 'resetPassword']",
    "\[AdminController::class, 'storeGrade'\]" => "[SchoolStructureController::class, 'storeGrade']",
    "\[AdminController::class, 'listClasses'\]" => "[SchoolStructureController::class, 'listClasses']",
    "\[AdminController::class, 'storeClass'\]" => "[SchoolStructureController::class, 'storeClass']",
    "\[AdminController::class, 'deleteClass'\]" => "[SchoolStructureController::class, 'deleteClass']",
    "\[AdminController::class, 'editClass'\]" => "[SchoolStructureController::class, 'editClass']",
    "\[AdminController::class, 'updateClass'\]" => "[SchoolStructureController::class, 'updateClass']",
    "\[AdminController::class, 'createClass'\]" => "[SchoolStructureController::class, 'createClass']",
    "\[AdminController::class, 'bulkTransfer'\]" => "[AdminStudentController::class, 'bulkTransfer']",
    "\[AdminController::class, 'showClassStudents'\]" => "[SchoolStructureController::class, 'showClassStudents']",
    "\[AdminController::class, 'showGradeReport'\]" => "[AdminReportController::class, 'showGradeReport']",
    "\[AdminController::class, 'gradeSettings'\]" => "[AdminSubjectController::class, 'gradeSettings']",
    "\[AdminController::class, 'storeGradeSettings'\]" => "[AdminSubjectController::class, 'storeGradeSettings']",
    "\[AdminController::class, 'updateSubject'\]" => "[AdminSubjectController::class, 'updateSubject']",
    "\[AdminController::class, 'updateSubjectClasses'\]" => "[AdminSubjectController::class, 'updateSubjectClasses']",
    "\[AdminController::class, 'storeSubject'\]" => "[AdminSubjectController::class, 'storeSubject']",
    "\[AdminController::class, 'listSubjects'\]" => "[AdminSubjectController::class, 'listSubjects']",
    "\[AdminController::class, 'deleteSubject'\]" => "[AdminSubjectController::class, 'deleteSubject']",
    "\[AdminController::class, 'editSubject'\]" => "[AdminSubjectController::class, 'editSubject']",
    "\[AdminController::class, 'storeAssignment'\]" => "[AdminSubjectController::class, 'storeAssignment']",
    "\[AdminController::class, 'removeAssignment'\]" => "[AdminSubjectController::class, 'removeAssignment']",
    "\[AdminController::class, 'updateAssignment'\]" => "[AdminSubjectController::class, 'updateAssignment']",
    "\[AdminController::class, 'createAssignment'\]" => "[AdminSubjectController::class, 'createAssignment']",
    "\[AdminController::class, 'getAvailableSubjects'\]" => "[AdminSubjectController::class, 'getAvailableSubjects']",
    "\[AdminController::class, 'listStudents'\]" => "[AdminStudentController::class, 'listStudents']",
    "\[AdminController::class, 'deleteStudent'\]" => "[AdminStudentController::class, 'deleteStudent']",
    "\[AdminController::class, 'admissionsIndex'\]" => "[AdminOperationController::class, 'admissionsIndex']",
    "\[AdminController::class, 'updateAdmissionStatus'\]" => "[AdminOperationController::class, 'updateAdmissionStatus']",
    "\[AdminController::class, 'leavesIndex'\]" => "[AdminOperationController::class, 'leavesIndex']",
    "\[AdminController::class, 'updateLeaveStatus'\]" => "[AdminOperationController::class, 'updateLeaveStatus']",
    "\[AdminController::class, 'walletIndex'\]" => "[AdminOperationController::class, 'walletIndex']",
    "\[AdminController::class, 'topupWallet'\]" => "[AdminOperationController::class, 'topupWallet']",
    "\[AdminController::class, 'purchaseWallet'\]" => "[AdminOperationController::class, 'purchaseWallet']",
    "\[AdminController::class, 'listUnassignedStudents'\]" => "[AdminStudentController::class, 'listUnassignedStudents']",
    "\[AdminController::class, 'updateStudentClass'\]" => "[AdminStudentController::class, 'updateStudentClass']",
    "\[AdminController::class, 'showCloseYear'\]" => "[AdminOperationController::class, 'showCloseYear']",
    "\[AdminController::class, 'executeCloseYear'\]" => "[AdminOperationController::class, 'executeCloseYear']",
    "\[AdminController::class, 'createParentLink'\]" => "[AdminParentController::class, 'createParentLink']",
    "\[AdminController::class, 'storeParentLink'\]" => "[AdminParentController::class, 'storeParentLink']",
    "\[AdminController::class, 'deleteParentLink'\]" => "[AdminParentController::class, 'deleteParentLink']",
    "\[AdminController::class, 'showSchedules'\]" => "[AdminScheduleController::class, 'showSchedules']",
    "\[AdminController::class, 'generateAutoSchedule'\]" => "[AdminScheduleController::class, 'generateAutoSchedule']",
    "\[AdminController::class, 'revertSchedule'\]" => "[AdminScheduleController::class, 'revertSchedule']",
    "\[AdminController::class, 'confirmSchedule'\]" => "[AdminScheduleController::class, 'confirmSchedule']",
    "\[AdminController::class, 'checkSwapAvailability'\]" => "[AdminScheduleController::class, 'checkSwapAvailability']",
    "\[AdminController::class, 'swapSchedules'\]" => "[AdminScheduleController::class, 'swapSchedules']",
    "\[AdminController::class, 'showPromotion'\]" => "[AdminOperationController::class, 'showPromotion']",
    "\[AdminController::class, 'previewPromotion'\]" => "[AdminOperationController::class, 'previewPromotion']",
    "\[AdminController::class, 'executePromotion'\]" => "[AdminOperationController::class, 'executePromotion']",
    "\[AdminController::class, 'checkAssessmentCompleteness'\]" => "[AdminOperationController::class, 'checkAssessmentCompleteness']",
    "\[AdminController::class, 'updateAcademicYear'\]" => "[AdminOperationController::class, 'updateAcademicYear']",
    "\[AdminController::class, 'showReports'\]" => "[AdminReportController::class, 'showReports']",
    "\[AdminController::class, 'printReport'\]" => "[AdminReportController::class, 'printReport']",
    "\[AdminController::class, 'printCertificate'\]" => "[AdminReportController::class, 'printCertificate']",
    "\[App\\\\Http\\\\Controllers\\\\AdminController::class, 'editMarks'\]" => "[AdminReportController::class, 'editMarks']",
    "\[App\\\\Http\\\\Controllers\\\\AdminController::class, 'updateMarks'\]" => "[AdminReportController::class, 'updateMarks']",
    "\[AdminController::class, 'preferences'\]" => "[AdminScheduleController::class, 'preferences']",
    "\[AdminController::class, 'editPreference'\]" => "[AdminScheduleController::class, 'editPreference']",
    "\[AdminController::class, 'updatePreference'\]" => "[AdminScheduleController::class, 'updatePreference']",
    "\[AdminController::class, 'toggleGrading'\]" => "[AdminOperationController::class, 'toggleGrading']"
];

foreach ($replacements as $pattern => $replacement) {
    $content = preg_replace('/' . $pattern . '/', $replacement, $content);
}

file_put_contents($routesFile, $content);
echo "Routes updated successfully.\n";
