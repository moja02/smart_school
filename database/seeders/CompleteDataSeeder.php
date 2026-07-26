<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\StudentProfile;
use App\Models\Assessment;
use App\Models\AssessmentMark;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Homework;
use App\Models\LeaveRequest;
use App\Models\WalletTransaction;
use App\Models\FeePayment;
use App\Models\Schedule;
use Faker\Factory as Faker;

class CompleteDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        $this->command->info('بدأ تنظيف وتوليد البيانات الشاملة للمدرسة...');

        // تعطيل قيود المفاتيح الأجنبية لتسهيل التنظيف وإعادة التوليد
        Schema::disableForeignKeyConstraints();

        // 1. تنظيف الجداول الفارغة أو التي نحتاج لتحديثها بالكامل
        DB::table('teacher_subject_section')->truncate();
        Schedule::truncate();
        Assessment::truncate();
        AssessmentMark::truncate();
        Homework::truncate();
        Exam::truncate();
        FeePayment::truncate();
        WalletTransaction::truncate();
        LeaveRequest::truncate();

        // تفعيل قيود المفاتيح الأجنبية مجدداً
        Schema::enableForeignKeyConstraints();

        // 2. جلب الكيانات الموجودة حالياً
        $teachers = User::role('teacher')->get();
        $parents = User::role('parent')->get();
        $students = StudentProfile::with('user')->get();
        $classes = SchoolClass::all();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $students->isEmpty() || $classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->error('يجب تشغيل DatabaseSeeder أولاً للتأكد من وجود المستخدمين والفصول الأساسية.');
            return;
        }

        $this->command->info('جاري إعداد إعدادات المواد وتوزيع الحصص الأسبوعية...');
        // 3. إعداد إعدادات المواد (weekly_classes = 4)
        foreach ($subjects as $subject) {
            DB::table('school_subject_settings')->updateOrInsert(
                ['school_id' => 1, 'subject_id' => $subject->id],
                ['weekly_classes' => 4, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('جاري إسناد المواد للمعلمين للشعب المختلفة...');
        // 4. إسناد المواد للمعلمين في الشعب (teacher_subject_section)
        $assignments = [];
        foreach ($classes as $class) {
            $gradeSubjects = $subjects->where('grade_id', $class->grade_id);
            foreach ($gradeSubjects as $subject) {
                // اختيار معلم عشوائي لهذه المادة في هذا الفصل
                $teacher = $teachers->random();
                
                DB::table('teacher_subject_section')->insert([
                    'school_id' => 1,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'section_id' => $class->id,
                    'teacher_name' => $teacher->name,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $assignments[] = (object) [
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                ];
            }
        }

        $this->command->info('جاري توليد الجدول الدراسي الخالي من التعارضات...');
        // 5. توليد جدول دراسي بدون تعارضات
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $periods = [1, 2, 3, 4, 5, 6];
        $teacherSlots = []; // [$teacher_id][$day][$period] = true

        foreach ($classes as $class) {
            $classAssignments = array_filter($assignments, function ($item) use ($class) {
                return $item->class_id == $class->id;
            });

            $classSlots = []; // [$day][$period] = true
            foreach ($classAssignments as $assign) {
                $sessionsToSchedule = 4; // 4 حصص أسبوعياً
                
                $shuffledDays = $days;
                shuffle($shuffledDays);

                $scheduledCount = 0;
                foreach ($shuffledDays as $day) {
                    $shuffledPeriods = $periods;
                    shuffle($shuffledPeriods);

                    foreach ($shuffledPeriods as $period) {
                        if ($scheduledCount >= $sessionsToSchedule) break 2;

                        $classFree = !isset($classSlots[$day][$period]);
                        $teacherFree = !isset($teacherSlots[$assign->teacher_id][$day][$period]);

                        if ($classFree && $teacherFree) {
                            Schedule::create([
                                'class_id' => $class->id,
                                'subject_id' => $assign->subject_id,
                                'teacher_id' => $assign->teacher_id,
                                'day' => $day,
                                'period' => $period,
                                'is_backup' => false
                            ]);

                            $classSlots[$day][$period] = true;
                            $teacherSlots[$assign->teacher_id][$day][$period] = true;
                            $scheduledCount++;
                        }
                    }
                }
            }
        }

        $this->command->info('جاري تحديث الرسوم والمدفوعات والمحافظ للطلاب...');
        // 6. تحديث الرسوم والمدفوعات والمحافظ للطلاب وتوليد الحركات المالية
        foreach ($students as $student) {
            $totalFees = 4500.00;
            $paidFees = 1500.00;
            
            // تحديث بروفايل الطالب
            $student->update([
                'total_fees' => $totalFees,
                'paid_fees' => $paidFees,
                'wallet_balance' => rand(80, 200) + 0.75,
                'daily_limit' => 25.00,
                'wallet_pin' => '1234'
            ]);

            // إيصال دفع الرسوم
            FeePayment::create([
                'student_id' => $student->id,
                'amount' => 1500.00,
                'payment_date' => now()->subDays(rand(10, 30))->format('Y-m-d'),
                'receipt_number' => 'RCP-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'notes' => 'قسط الرسوم الدراسية الأول'
            ]);

            // حركات المحفظة
            WalletTransaction::create([
                'student_id' => $student->id,
                'amount' => 250.00,
                'type' => 'topup',
                'description' => 'شحن رصيد المحفظة من قبل ولي الأمر'
            ]);
            WalletTransaction::create([
                'student_id' => $student->id,
                'amount' => 12.50,
                'type' => 'purchase',
                'description' => 'شراء وجبة فطور من المقصف المدرسى'
            ]);
            WalletTransaction::create([
                'student_id' => $student->id,
                'amount' => 8.00,
                'type' => 'purchase',
                'description' => 'شراء عصير بارد من المقصف'
            ]);

            // 7. طلبات الاستئذان من أولياء الأمور
            // جلب ولي الأمر المرتبط بالطالب
            $parentLink = DB::table('parent_student')->where('student_id', $student->user_id)->first();
            if ($parentLink) {
                LeaveRequest::create([
                    'parent_id' => $parentLink->parent_id,
                    'student_id' => $student->id, // يشير لـ student_profiles.id
                    'type' => $faker->randomElement(['absence', 'early_leave']),
                    'leave_date' => now()->addDays(rand(1, 5))->format('Y-m-d'),
                    'reason' => 'مراجعة طبية مجدولة مسبقاً وتعب عام للطالب',
                    'status' => $faker->randomElement(['pending', 'approved', 'rejected'])
                ]);
            }
        }

        $this->command->info('جاري إعداد التقييمات الشهرية والواجبات المنزلية والامتحانات...');
        // 8. إعداد التقييمات الشهرية والدرجات المرتبطة بها
        foreach ($classes as $class) {
            $classAssignments = array_filter($assignments, function ($item) use ($class) {
                return $item->class_id == $class->id;
            });

            foreach ($classAssignments as $assign) {
                // تقييم 1: واجب منزلي (20 درجة)
                $ass1 = Assessment::create([
                    'subject_id' => $assign->subject_id,
                    'section_id' => $class->id,
                    'teacher_id' => $assign->teacher_id,
                    'name' => 'الواجب الأول - أعمال السنة',
                    'semester' => 1,
                    'max_score' => 20
                ]);

                // تقييم 2: اختبار أعمال السنة (20 درجة)
                $ass2 = Assessment::create([
                    'subject_id' => $assign->subject_id,
                    'section_id' => $class->id,
                    'teacher_id' => $assign->teacher_id,
                    'name' => 'الاختبار التحريري الأول',
                    'semester' => 1,
                    'max_score' => 20
                ]);

                // رصد درجات الطلاب في الفصل لهذين التقييمين
                $classStudents = StudentProfile::where('class_id', $class->id)->get();
                foreach ($classStudents as $studentProfile) {
                    AssessmentMark::create([
                        'assessment_id' => $ass1->id,
                        'student_id' => $studentProfile->user_id, // يشير لـ users.id
                        'score' => rand(15, 20)
                    ]);

                    AssessmentMark::create([
                        'assessment_id' => $ass2->id,
                        'student_id' => $studentProfile->user_id,
                        'score' => rand(14, 20)
                    ]);
                }

                // 9. الواجبات المنزلية
                $subject = $subjects->find($assign->subject_id);
                Homework::create([
                    'teacher_id' => $assign->teacher_id,
                    'subject_id' => $assign->subject_id,
                    'class_id' => $class->id,
                    'title' => 'واجب في مادة ' . $subject->name,
                    'description' => 'حل التمارين والمسائل من صفحة 15 إلى صفحة 22 في كتاب المادة.',
                    'due_date' => now()->addDays(rand(2, 6))->format('Y-m-d')
                ]);

                // 10. الامتحانات الأساسية
                Exam::create([
                    'title' => 'امتحان منتصف الفصل الدراسي - ' . $subject->name,
                    'exam_date' => now()->addDays(rand(7, 15))->format('Y-m-d'),
                    'max_score' => 50,
                    'subject_id' => $assign->subject_id,
                    'class_id' => $class->id
                ]);
            }
        }

        $this->command->info('✅ تم توليد وتعبئة كافة بيانات المدرسة بنجاح!');
        $this->command->info('الجدول الدراسي، التقييمات، الامتحانات، المدفوعات، والواجبات جاهزة للتشغيل والاستخدام.');
    }
}
