<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $user = $request->user();

        // Monthly summary
        $summary = Transaction::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->first();

        $income = (float) ($summary->income ?? 0);
        $expense = (float) ($summary->expense ?? 0);

        $current = Carbon::create($year, $month, 1);
        $previous = $current->copy()->subMonth();
        $prevSummary = Transaction::where('user_id', $user->id)
            ->whereYear('date', $previous->year)
            ->whereMonth('date', $previous->month)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->first();

        $prevIncome = (float) ($prevSummary->income ?? 0);
        $prevExpense = (float) ($prevSummary->expense ?? 0);
        $prevBalance = $prevIncome - $prevExpense;

        // Expenses by category (for chart)
        $byCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn($t) => [
                'name' => $t->category->name,
                'icon' => $t->category->icon,
                'color' => $t->category->color,
                'total' => (float) $t->total,
            ]);

        // Recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'amount' => (float) $t->amount,
                'type' => $t->type,
                'description' => $t->description,
                'date' => $t->date->format('Y-m-d'),
                'category' => [
                    'name' => $t->category->name,
                    'icon' => $t->category->icon,
                    'color' => $t->category->color,
                ],
            ]);

        // Budgets with spent amounts
        $budgets = \App\Models\Budget::where('user_id', $user->id)
            ->where('month', sprintf('%04d-%02d', $year, $month))
            ->with('category')
            ->get()
            ->map(function ($b) use ($user, $year, $month) {
                $spent = (float) Transaction::where('user_id', $user->id)
                    ->where('category_id', $b->category_id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount');
                return [
                    'category' => $b->category->name,
                    'color' => $b->category->color,
                    'limit' => (float) $b->limit_amount,
                    'spent' => $spent,
                    'remaining' => (float) $b->limit_amount - $spent,
                ];
            });

        $balance = $income - $expense;

        return Inertia::render('Dashboard', [
            'year' => (int) $year,
            'month' => (int) $month,
            'isCurrentMonth' => $current->isSameMonth(now()),
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'previous' => [
                'income' => $prevIncome,
                'expense' => $prevExpense,
                'balance' => $prevBalance,
            ],
            'byCategory' => $byCategory,
            'recentTransactions' => $recentTransactions,
            'budgets' => $budgets,
        ]);
    }
}
