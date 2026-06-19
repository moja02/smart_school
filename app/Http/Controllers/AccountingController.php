<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\FeePayment;

class AccountingController extends Controller
{
    public function expensesIndex()
    {
        $schoolId = auth()->user()->school_id;
        $expenses = Expense::where('school_id', $schoolId)->orderBy('expense_date', 'desc')->get();
        return view('admin.accounting.expenses', compact('expenses'));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.1',
            'expense_date' => 'required|date',
        ]);

        Expense::create([
            'school_id' => auth()->user()->school_id,
            'title' => $request->title,
            'category' => $request->category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'تم تسجيل المصروف بنجاح.');
    }

    public function profitLossReport()
    {
        $schoolId = auth()->user()->school_id;
        
        // 1. حساب الإيرادات (Revenues) من الرسوم المدرسية المدفوعة
        $totalRevenues = FeePayment::whereHas('student', function($q) use ($schoolId) {
            $q->whereHas('user', function($q2) use ($schoolId) {
                $q2->where('school_id', $schoolId);
            });
        })->sum('amount');
        
        // 2. حساب المصروفات (Expenses)
        $totalExpenses = Expense::where('school_id', $schoolId)->sum('amount');

        // 3. الصافي (Net Profit/Loss)
        $netProfit = $totalRevenues - $totalExpenses;

        $currentYear = date('Y');
        
        $allRevenues = FeePayment::whereHas('student', function($q) use ($schoolId) {
            $q->whereHas('user', function($q2) use ($schoolId) {
                $q2->where('school_id', $schoolId);
            });
        })->whereYear('payment_date', $currentYear)->get();
                            
        $monthlyRevenues = [];
        foreach($allRevenues as $rev) {
            $month = (int)date('m', strtotime($rev->payment_date));
            $monthlyRevenues[$month] = ($monthlyRevenues[$month] ?? 0) + $rev->amount;
        }

        $allExpenses = Expense::where('school_id', $schoolId)
                            ->whereYear('expense_date', $currentYear)
                            ->get();
                            
        $monthlyExpenses = [];
        foreach($allExpenses as $exp) {
            $month = (int)date('m', strtotime($exp->expense_date));
            $monthlyExpenses[$month] = ($monthlyExpenses[$month] ?? 0) + $exp->amount;
        }

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData['labels'][] = 'شهر ' . $i;
            $chartData['revenues'][] = $monthlyRevenues[$i] ?? 0;
            $chartData['expenses'][] = $monthlyExpenses[$i] ?? 0;
        }

        return view('admin.accounting.report', compact('totalRevenues', 'totalExpenses', 'netProfit', 'chartData', 'currentYear'));
    }
}
