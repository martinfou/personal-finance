<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(25);

        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('type')->orderBy('name')->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $validated['user_id'] = $request->user()->id;

        Transaction::create($validated);

        return redirect()->back()->with('success', 'Transaction added!');
    }

    public function show(Transaction $transaction)
    {
        //
    }

    public function edit(Transaction $transaction)
    {
        //
    }

    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->back()->with('success', 'Transaction deleted.');
    }
}
