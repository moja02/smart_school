<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم الأدمن
     */
    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $totalStudents = User::role('student')->where('school_id', $schoolId)->count();
        $totalTeachers = User::role('teacher')->where('school_id', $schoolId)->count();
        $totalParents  = User::role('parent')->where('school_id', $schoolId)->count();
        $classes = SchoolClass::where('school_id', $schoolId)->count();
        
        // --- 📊 Analytics Data Preparation ---
        
        // 1. Revenue & Expenses over the last 6 months
        $months = [];
        $revenueData = [];
        $expenseData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('F Y'); // e.g., "June 2026"
            
            // Revenue for this month (Assuming FeePayment model exists, joining with students to filter by school_id)
            $revenue = \App\Models\FeePayment::whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->whereHas('student', function($q) use ($schoolId) {
                    // student relationship points to student_profiles, which belongs to a user
                    $q->whereHas('user', function($q2) use ($schoolId) {
                        $q2->where('school_id', $schoolId);
                    });
                })->sum('amount');
                
            $revenueData[] = (float) $revenue;
            
            // Expenses for this month
            $expense = \App\Models\Expense::where('school_id', $schoolId)
                ->whereMonth('expense_date', $date->month)
                ->whereYear('expense_date', $date->year)
                ->sum('amount');
                
            $expenseData[] = (float) $expense;
        }

        // 2. User Demographics Data (Pie Chart)
        $usersDemographics = [
            'الطلاب' => $totalStudents,
            'المعلمون' => $totalTeachers,
            'أولياء الأمور' => $totalParents
        ];
        
        return view('admin.dashboard', compact(
            'totalStudents', 'totalTeachers', 'totalParents', 'classes',
            'months', 'revenueData', 'expenseData', 'usersDemographics'
        ));
    }
}
