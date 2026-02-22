<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    // عرض صفحة التقويم
    public function index($subject_id, $section_id)
{
    $subject = DB::table('subjects')->where('id', $subject_id)->first();
    // جلب الشعبة (الفصل) باستخدام section_id
    $section = DB::table('classes')->where('id', $section_id)->first(); 

    if (!$subject || !$section) {
        return redirect()->back()->with('error', 'بيانات غير صحيحة');
    }

    return view('teacher.schedule.index', compact('subject', 'section'));
}

    public function getEvents($subject_id, $section_id)
    {
        $teacher_id = Auth::id();

        $exams = DB::table('exams')
            ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->where('exams.section_id', $section_id)
            ->select('exams.*', 'subjects.name as subject_name')
            ->get();

        $events = [];

        foreach ($exams as $exam) {
            $isMyExam = ($exam->teacher_id == $teacher_id);
            $isCurrentSubject = ($exam->subject_id == $subject_id);

            if ($isMyExam) {
                $events[] = [
                    'id' => $exam->id,
                    'title' => $isCurrentSubject ? $exam->title : $exam->title . ' (' . $exam->subject_name . ')',
                    'start' => $exam->exam_date,
                    'backgroundColor' => $isCurrentSubject ? '#0d6efd' : '#0dcaf0', 
                    'borderColor' => $isCurrentSubject ? '#0d6efd' : '#0dcaf0',
                    // ✅ السطر الجديد: هذا يفعل خاصية السحب والإفلات برمجياً
                    'editable' => true, 
                    'extendedProps' => [
                        'canEdit' => true,
                        'realTitle' => $exam->title,
                        'subjectName' => $exam->subject_name
                    ]
                ];
            } else {
                $events[] = [
                    'id' => $exam->id,
                    'title' => "امتحان: " . $exam->subject_name,
                    'start' => $exam->exam_date,
                    'backgroundColor' => '#6c757d',
                    'borderColor' => '#6c757d',
                    // ❌ منع السحب والإفلات لمواعيد الأساتذة الآخرين
                    'editable' => false, 
                    'extendedProps' => [
                        'canEdit' => false,
                        'subjectName' => $exam->subject_name
                    ]
                ];
            }
        }

        return response()->json($events);
    }

    // حفظ امتحان جديد
    public function store(Request $request)
    {
        // 1. التحقق من التضارب: هل يوجد امتحان آخر لنفس الشعبة في نفس اليوم؟
        $exists = DB::table('exams')
            ->where('section_id', $request->section_id)
            ->where('exam_date', $request->exam_date)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false, 
                'message' => 'عذراً، يوجد امتحان آخر لهذه الشعبة في هذا التاريخ!'
            ]);
        }

        // 2. الحفظ
        DB::table('exams')->insert([
            'title' => $request->title,
            'exam_date' => $request->exam_date,
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'teacher_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // تعديل امتحان
    public function update(Request $request)
    {
        $exam = DB::table('exams')->where('id', $request->exam_id)->first();

        if (!$exam || $exam->teacher_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذا الامتحان']);
        }

        // إذا تم إرسال تاريخ جديد، تأكد أنه لا يوجد امتحان آخر في هذا اليوم
        if ($request->has('exam_date') && $request->exam_date != $exam->exam_date) {
            $exists = DB::table('exams')
                ->where('section_id', $exam->section_id)
                ->where('exam_date', $request->exam_date)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false, 
                    'message' => 'عذراً، يوجد امتحان آخر لهذه الشعبة في هذا اليوم!'
                ]);
            }
        }

        DB::table('exams')->where('id', $request->exam_id)->update([
            'title' => $request->title ?? $exam->title,
            'exam_date' => $request->exam_date ?? $exam->exam_date, // تحديث التاريخ
            'updated_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    // حذف امتحان
    public function delete(Request $request)
    {
        $exam = DB::table('exams')->where('id', $request->exam_id)->first();

        if (!$exam || $exam->teacher_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف هذا الامتحان']);
        }

        DB::table('exams')->where('id', $request->exam_id)->delete();
        
        return response()->json(['success' => true]);
    }
}