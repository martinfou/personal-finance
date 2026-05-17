<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        $recurrings = RecurringTransaction::where('user_id', $request->user()->id)
            ->with('category')
            ->orderBy('next_date')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'amount' => (float) $r->amount,
                'type' => $r->type,
                'description' => $r->description,
                'frequency' => $r->frequency,
                'day_of_month' => $r->day_of_month,
                'next_date' => $r->next_date->format('Y-m-d'),
                'is_active' => $r->is_active,
                'category' => ['id' => $r->category->id, 'name' => $r->category->name, 'icon' => $r->category->icon],
            ]);

        $categories = Category::where('user_id', $request->user()->id)->orderBy('type')->orderBy('name')->get();

        return Inertia::render('Recurring/Index', [
            'recurrings' => $recurrings,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'day_of_month' => 'nullable|integer|between:1,31',
            'next_date' => 'required|date',
        ]);

        $validated['user_id'] = $request->user()->id;
        RecurringTransaction::create($validated);

        return redirect()->back()->with('success', 'Transaction récurrente créée!');
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
            'amount' => 'numeric|min:0.01',
            'next_date' => 'date',
        ]);

        $recurringTransaction->update($validated);
        return redirect()->back()->with('success', 'Mise à jour effectuée.');
    }

    public function destroy(RecurringTransaction $recurringTransaction)
    {
        $recurringTransaction->delete();
        return redirect()->back()->with('success', 'Transaction récurrente supprimée.');
    }

    public function process(Request $request)
    {
        $user = $request->user();
        $now = now()->startOfDay();
        $due = RecurringTransaction::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('next_date', '<=', $now)
            ->get();

        $created = 0;
        foreach ($due as $r) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $r->category_id,
                'amount' => $r->amount,
                'type' => $r->type,
                'description' => $r->description . ' (récurrent)',
                'date' => $r->next_date,
            ]);

            // Calculate next date
            $next = match ($r->frequency) {
                'daily' => $r->next_date->addDay(),
                'weekly' => $r->next_date->addWeek(),
                'monthly' => $r->next_date->addMonth(),
                'yearly' => $r->next_date->addYear(),
            };
            $r->update(['next_date' => $next]);
            $created++;
        }

        return redirect()->back()->with('success', "$created transactions récurrentes créées.");
    }
}
