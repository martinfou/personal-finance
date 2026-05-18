<?php

namespace App\Http\Controllers;

use App\Models\SavingGoal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavingGoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = SavingGoal::where('user_id', $request->user()->id)
            ->orderBy('deadline')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'target_amount' => (float) $g->target_amount,
                'current_amount' => (float) $g->current_amount,
                'progress' => $g->target_amount > 0 ? round(($g->current_amount / $g->target_amount) * 100, 1) : 0,
                'remaining' => max(0, (float) $g->target_amount - (float) $g->current_amount),
                'deadline' => $g->deadline?->format('Y-m-d'),
                'days_left' => $g->deadline ? now()->startOfDay()->diffInDays($g->deadline, false) : null,
            ]);

        return Inertia::render('Goals/Index', ['goals' => $goals]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after:today',
        ]);

        $validated['user_id'] = $request->user()->id;
        SavingGoal::create($validated);

        return redirect()->back()->with('success', 'Objectif d\'épargne créé!');
    }

    public function update(Request $request, SavingGoal $savingGoal)
    {
        $validated = $request->validate([
            'current_amount' => 'required|numeric|min:0',
        ]);

        $savingGoal->update($validated);
        return redirect()->back()->with('success', 'Objectif mis à jour!');
    }

    public function destroy(SavingGoal $savingGoal)
    {
        $savingGoal->delete();
        return redirect()->back()->with('success', 'Objectif supprimé.');
    }
}
