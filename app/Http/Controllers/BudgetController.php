<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $monthStr = sprintf('%04d-%02d', $year, $month);

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $monthStr)
            ->with('category')
            ->get()
            ->map(function ($b) use ($user, $monthStr) {
                $spent = (float) Transaction::where('user_id', $user->id)
                    ->where('category_id', $b->category_id)
                    ->whereYear('date', substr($monthStr, 0, 4))
                    ->whereMonth('date', substr($monthStr, 5, 2))
                    ->sum('amount');
                return [
                    'id' => $b->id,
                    'category_id' => $b->category_id,
                    'category_name' => $b->category->name,
                    'category_icon' => $b->category->icon,
                    'category_color' => $b->category->color,
                    'limit' => (float) $b->limit_amount,
                    'spent' => $spent,
                    'remaining' => (float) $b->limit_amount - $spent,
                    'percentage' => $b->limit_amount > 0 ? round(($spent / $b->limit_amount) * 100, 1) : 0,
                ];
            });

        $categories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        $monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'categories' => $categories,
            'year' => (int) $year,
            'month' => (int) $month,
            'monthLabel' => $monthNames[$month - 1] . ' ' . $year,
            'monthStr' => $monthStr,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:1',
            'month' => 'required|string|size:7',
        ]);

        $validated['user_id'] = $request->user()->id;

        Budget::updateOrCreate(
            ['user_id' => $validated['user_id'], 'category_id' => $validated['category_id'], 'month' => $validated['month']],
            ['limit_amount' => $validated['limit_amount']]
        );

        return redirect()->back()->with('success', 'Budget enregistré!');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->back()->with('success', 'Budget supprimé.');
    }
}
