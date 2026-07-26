<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\TeacherPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function showSchedules(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId)->get();
        $selectedClassId = $request->class_id ?? ($classes->first()->id ?? null);
        
        $schedules = collect();
        $backupSchedules = collect();
        $hasBackup = false;

        if ($selectedClassId) {
            $schedules = Schedule::where('class_id', $selectedClassId)
                                 ->where('is_backup', 0)
                                 ->with(['subject', 'teacher'])
                                 ->get();
                                 
            $backupSchedules = Schedule::where('class_id', $selectedClassId)
                                       ->where('is_backup', 1)
                                       ->with(['subject', 'teacher'])
                                       ->get();
                                       
            $hasBackup = Schedule::where('class_id', $selectedClassId)->where('is_backup', 1)->exists();
        }

        $teachers = User::role('teacher')->where('school_id', $schoolId)->get();
        $periods = [1, 2, 3, 4, 5, 6, 7];
        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

        return view('admin.schedules.index', compact('classes', 'schedules', 'backupSchedules', 'selectedClassId', 'teachers', 'hasBackup', 'periods', 'days'));
    }

    public function generateAutoSchedule()
    {
        $schoolId = auth()->user()->school_id;

        $teachers = User::role('teacher')->where('school_id', $schoolId)->get();
        $classes = SchoolClass::where('school_id', $schoolId)->get();
        $subjects = DB::table('school_subject_settings')->where('school_id', $schoolId)->get();

        if ($teachers->isEmpty() || $classes->isEmpty() || $subjects->isEmpty()) {
            return back()->with('error', 'يجب إعداد المعلمين والفصول والمواد قبل إنشاء الجدول.');
        }

        $schoolClassIds = $classes->pluck('id');
        Schedule::whereIn('class_id', $schoolClassIds)->where('is_backup', 1)->delete();
        Schedule::whereIn('class_id', $schoolClassIds)->where('is_backup', 0)->update(['is_backup' => 1]);

        $assignments = DB::table('teacher_subject_section')
            ->where('school_id', $schoolId)
            ->get();

        $preferences = TeacherPreference::whereIn('teacher_id', $teachers->pluck('id'))->get();

        $constraints = [
            'teachers' => $teachers->pluck('id')->toArray(),
            'classes' => $classes->pluck('id')->toArray(),
            'assignments' => $assignments->toArray(),
            'subjects' => $subjects->toArray(),
            'preferences' => $preferences->toArray()
        ];

        $jsonPath = storage_path('app/constraints.json');
        file_put_contents($jsonPath, json_encode($constraints));

        $pythonScriptPath = base_path('scripts/scheduler.py');
        $command = escapeshellcmd("python \"$pythonScriptPath\" \"$jsonPath\"");
        $output = shell_exec($command);

        $scheduleResult = json_decode($output, true);

        if (!$scheduleResult || !is_array($scheduleResult)) {
            Schedule::whereIn('class_id', $schoolClassIds)->where('is_backup', 1)->update(['is_backup' => 0]);
            return back()->with('error', 'حدث خطأ في الخوارزمية ولم يتم إنشاء الجدول. تم استرجاع الجدول القديم.');
        }

        $newSchedules = [];
        foreach ($scheduleResult as $item) {
            $newSchedules[] = [
                'class_id'   => $item['class_id'],
                'subject_id' => $item['subject_id'],
                'teacher_id' => $item['teacher_id'],
                'day'        => $item['day'],
                'period'     => $item['period'],
                'is_backup'  => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($newSchedules) > 0) {
            Schedule::insert($newSchedules);
        }

        return back()->with('success', 'تم توليد الجدول الجديد بنجاح باستخدام الذكاء الاصطناعي 🤖✨ يرجى مراجعته قبل الاعتماد.');
    }

    public function revertSchedule()
    {
        $schoolId = auth()->user()->school_id;
        $schoolClassIds = SchoolClass::where('school_id', $schoolId)->pluck('id');

        Schedule::whereIn('class_id', $schoolClassIds)->where('is_backup', 0)->delete();
        Schedule::whereIn('class_id', $schoolClassIds)->where('is_backup', 1)->update(['is_backup' => 0]);

        return back()->with('success', 'تم التراجع بنجاح! عاد الجدول القديم للعمل ⏪');
    }

    public function confirmSchedule()
    {
        $schoolId = auth()->user()->school_id;
        $schoolClassIds = SchoolClass::where('school_id', $schoolId)->pluck('id');

        Schedule::whereIn('class_id', $schoolClassIds)->where('is_backup', 1)->delete();

        return back()->with('success', 'تم اعتماد الجدول الجديد وحذف النسخة القديمة نهائياً ✅');
    }

    public function preferencesList()
    {
        $schoolId = auth()->user()->school_id;
        $teachers = User::role('teacher')->where('school_id', $schoolId)->get();

        return view('admin.schedules.preferences_list', compact('teachers'));
    }

    public function preferences(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $teachers = User::role('teacher')->where('school_id', $schoolId)->get();

        $selectedTeacherId = $request->teacher_id;
        $preferences = collect();

        if ($selectedTeacherId) {
            $preferences = TeacherPreference::where('teacher_id', $selectedTeacherId)->get();
        }

        return view('admin.schedules.preferences', compact('teachers', 'selectedTeacherId', 'preferences'));
    }

    public function editPreference($id)
    {
        $teacher = User::findOrFail($id);
        $preferences = TeacherPreference::where('teacher_id', $teacher->id)->get()->keyBy('day_name');
        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        
        return view('admin.schedules.edit_preference', compact('teacher', 'preferences', 'days'));
    }

    public function updatePreference(Request $request, $id)
    {
        return $this->storePreference($request, $id);
    }

    public function storePreference(Request $request, $id)
    {
        $teacher = User::findOrFail($id);
        
        TeacherPreference::where('teacher_id', $teacher->id)->delete();

        if ($request->has('preferences')) {
            foreach ($request->preferences as $day => $data) {
                $isDayOff = isset($data['is_day_off']) ? true : false;
                $blockedPeriods = isset($data['blocked_periods']) ? $data['blocked_periods'] : [];

                if ($isDayOff || !empty($blockedPeriods)) {
                    TeacherPreference::create([
                        'teacher_id' => $teacher->id,
                        'day_name' => $day,
                        'is_day_off' => $isDayOff,
                        'blocked_periods' => $blockedPeriods,
                    ]);
                }
            }
        }

        return redirect()->route('admin.schedules.preferences')
            ->with('success', 'تم حفظ التفضيلات بنجاح للأستاذ: ' . $teacher->name);
    }

    public function checkSwapAvailability(Request $request)
    {
        $request->validate([
            'schedule_a_id' => 'required|exists:schedules,id',
            'schedule_b_id' => 'required|exists:schedules,id',
        ]);

        $scheduleA = Schedule::with(['subject', 'teacher', 'schoolClass'])->findOrFail($request->schedule_a_id);
        $scheduleB = Schedule::with(['subject', 'teacher', 'schoolClass'])->findOrFail($request->schedule_b_id);

        if ($scheduleA->id === $scheduleB->id) {
            return response()->json(['valid' => false, 'message' => 'لا يمكنك تبديل حصة مع نفسها!']);
        }

        if ($scheduleA->day === $scheduleB->day && $scheduleA->period === $scheduleB->period) {
            return response()->json(['valid' => false, 'message' => 'الحصتان في نفس الوقت بالفعل، لا حاجة للتبديل.']);
        }

        $conflicts = [];

        $teacherAConflict = Schedule::where('teacher_id', $scheduleA->teacher_id)
            ->where('day', $scheduleB->day)->where('period', $scheduleB->period)
            ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)
            ->with('schoolClass')->first();

        if ($teacherAConflict) {
            $className = $teacherAConflict->schoolClass->name ?? '?';
            $conflicts[] = "الأستاذ ({$scheduleA->teacher->name}) لديه حصة في فصل ({$className}) يوم {$scheduleB->day} الحصة {$scheduleB->period}.";
        }

        $teacherBConflict = Schedule::where('teacher_id', $scheduleB->teacher_id)
            ->where('day', $scheduleA->day)->where('period', $scheduleA->period)
            ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)
            ->with('schoolClass')->first();

        if ($teacherBConflict) {
            $className = $teacherBConflict->schoolClass->name ?? '?';
            $conflicts[] = "الأستاذ ({$scheduleB->teacher->name}) لديه حصة في فصل ({$className}) يوم {$scheduleA->day} الحصة {$scheduleA->period}.";
        }

        if ($scheduleA->class_id !== $scheduleB->class_id) {
            $classAConflict = Schedule::where('class_id', $scheduleA->class_id)
                ->where('day', $scheduleB->day)->where('period', $scheduleB->period)
                ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->first();

            if ($classAConflict) {
                $className = $scheduleA->schoolClass->name ?? '?';
                $conflicts[] = "فصل ({$className}) لديه حصة أخرى يوم {$scheduleB->day} الحصة {$scheduleB->period}.";
            }

            $classBConflict = Schedule::where('class_id', $scheduleB->class_id)
                ->where('day', $scheduleA->day)->where('period', $scheduleA->period)
                ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->first();

            if ($classBConflict) {
                $className = $scheduleB->schoolClass->name ?? '?';
                $conflicts[] = "فصل ({$className}) لديه حصة أخرى يوم {$scheduleA->day} الحصة {$scheduleA->period}.";
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'valid' => false,
                'message' => 'يوجد تعارض يمنع التبديل:',
                'conflicts' => $conflicts,
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'التبديل متاح ✅',
            'details' => [
                'a' => [
                    'subject' => $scheduleA->subject->name ?? 'مادة',
                    'teacher' => $scheduleA->teacher->name ?? 'أستاذ',
                    'class'   => $scheduleA->schoolClass->name ?? 'فصل',
                    'day'     => $scheduleA->day,
                    'period'  => $scheduleA->period,
                ],
                'b' => [
                    'subject' => $scheduleB->subject->name ?? 'مادة',
                    'teacher' => $scheduleB->teacher->name ?? 'أستاذ',
                    'class'   => $scheduleB->schoolClass->name ?? 'فصل',
                    'day'     => $scheduleB->day,
                    'period'  => $scheduleB->period,
                ],
            ],
        ]);
    }

    public function swapSchedules(Request $request)
    {
        $request->validate([
            'schedule_a_id' => 'required|exists:schedules,id',
            'schedule_b_id' => 'required|exists:schedules,id',
        ]);

        $scheduleA = Schedule::findOrFail($request->schedule_a_id);
        $scheduleB = Schedule::findOrFail($request->schedule_b_id);

        if ($scheduleA->id === $scheduleB->id) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك تبديل حصة مع نفسها!']);
        }

        $teacherAConflict = Schedule::where('teacher_id', $scheduleA->teacher_id)
            ->where('day', $scheduleB->day)->where('period', $scheduleB->period)
            ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

        $teacherBConflict = Schedule::where('teacher_id', $scheduleB->teacher_id)
            ->where('day', $scheduleA->day)->where('period', $scheduleA->period)
            ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

        if ($teacherAConflict || $teacherBConflict) {
            return response()->json(['success' => false, 'message' => 'يوجد تعارض في جدول أحد الأساتذة! لم يتم التبديل.']);
        }

        if ($scheduleA->class_id !== $scheduleB->class_id) {
            $classAConflict = Schedule::where('class_id', $scheduleA->class_id)
                ->where('day', $scheduleB->day)->where('period', $scheduleB->period)
                ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

            $classBConflict = Schedule::where('class_id', $scheduleB->class_id)
                ->where('day', $scheduleA->day)->where('period', $scheduleA->period)
                ->where('id', '!=', $scheduleA->id)->where('id', '!=', $scheduleB->id)->exists();

            if ($classAConflict || $classBConflict) {
                return response()->json(['success' => false, 'message' => 'يوجد تعارض في جدول أحد الفصول! لم يتم التبديل.']);
            }
        }

        DB::transaction(function () use ($scheduleA, $scheduleB) {
            $tempDay = $scheduleA->day;
            $tempPeriod = $scheduleA->period;

            $scheduleA->update([
                'day' => $scheduleB->day,
                'period' => $scheduleB->period,
            ]);

            $scheduleB->update([
                'day' => $tempDay,
                'period' => $tempPeriod,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'تم تبديل الحصتين بنجاح! 🔄']);
    }
}
