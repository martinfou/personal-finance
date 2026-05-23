<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BudgetReportService
{
    /**
     * Get monthly budget review for a user.
     *
     * @return array{month: string, year: int, monthLabel: string,
     *     income: float, expense: float, balance: float,
     *     savingsRate: float, categories: Collection, alerts: Collection,
     *     previousMonth: array|null}
     */
    public function monthlyReview(int $userId, int $year, int $month): array
    {
        $monthStr = sprintf('%04d-%02d', $year, $month);
        $date = Carbon::create($year, $month, 1);
        $prev = $date->copy()->subMonth();

        // Monthly income / expense totals
        $summary = Transaction::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->first();

        $income = (float) ($summary->income ?? 0);
        $expense = (float) ($summary->expense ?? 0);
        $balance = $income - $expense;
        $savingsRate = $income > 0 ? round(($balance / $income) * 100, 1) : 0;

        // Budgets with actuals
        $budgets = Budget::where('user_id', $userId)
            ->where('month', $monthStr)
            ->with('category')
            ->get();

        $categories = collect();
        $alerts = collect();

        foreach ($budgets as $budget) {
            $spent = (float) Transaction::where('user_id', $userId)
                ->where('category_id', $budget->category_id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $limit = (float) $budget->limit_amount;
            $percentage = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0;
            $remaining = $limit - $spent;

            $status = 'on_track';
            if ($percentage >= 100) {
                $status = 'overspent';
            } elseif ($percentage >= 80) {
                $status = 'warning';
            }

            $categories->push([
                'id' => $budget->category_id,
                'category_name' => $budget->category->name,
                'category_icon' => $budget->category->icon,
                'category_color' => $budget->category->color,
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'percentage' => $percentage,
                'status' => $status,
            ]);

            // Generate alerts
            if ($status === 'overspent') {
                $alerts->push([
                    'type' => 'danger',
                    'category_name' => $budget->category->name,
                    'icon' => $budget->category->icon,
                    'message' => "{$budget->category->name}: " . number_format($spent - $limit, 2) . "\$ dépassé!",
                    'percentage' => $percentage,
                ]);
            } elseif ($status === 'warning') {
                $alerts->push([
                    'type' => 'warning',
                    'category_name' => $budget->category->name,
                    'icon' => $budget->category->icon,
                    'message' => "{$budget->category->name}: {$percentage}% du budget utilisé",
                    'percentage' => $percentage,
                ]);
            }
        }

        // Previous month comparison
        $prevSummary = Transaction::where('user_id', $userId)
            ->whereYear('date', $prev->year)
            ->whereMonth('date', $prev->month)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->first();

        $prevIncome = (float) ($prevSummary->income ?? 0);
        $prevExpense = (float) ($prevSummary->expense ?? 0);
        $prevBalance = $prevIncome - $prevExpense;
        $prevSavingsRate = $prevIncome > 0 ? round(($prevBalance / $prevIncome) * 100, 1) : 0;

        $previousMonth = $prev->isBefore(Carbon::create(2025, 1, 1))
            ? null
            : [
                'year' => (int) $prev->year,
                'month' => (int) $prev->month,
                'income' => $prevIncome,
                'expense' => $prevExpense,
                'balance' => $prevBalance,
                'savingsRate' => $prevSavingsRate,
            ];

        $monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin',
                       'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        return [
            'year' => $year,
            'month' => $month,
            'monthLabel' => ($monthNames[$month - 1] ?? '???') . " {$year}",
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'savingsRate' => $savingsRate,
            'categories' => $categories->sortBy('category_name')->values(),
            'alerts' => $alerts->sortByDesc('percentage')->values(),
            'previousMonth' => $previousMonth,
        ];
    }

    /**
     * Get available months with budget data.
     *
     * @return Collection<int, array{year: int, month: int, label: string}>
     */
    public function availableMonths(int $userId): Collection
    {
        $months = Budget::where('user_id', $userId)
            ->selectRaw('DISTINCT month')
            ->orderByDesc('month')
            ->pluck('month');

        $monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin',
                       'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        return $months->map(function (string $m) use ($monthNames) {
            [$y, $mo] = explode('-', $m);
            $moIdx = (int) ltrim($mo, '0') - 1;
            return [
                'year' => (int) $y,
                'month' => (int) $mo,
                'label' => ($monthNames[$moIdx] ?? '???') . " {$y}",
            ];
        });
    }

    /**
     * Generate CSV export content for a monthly budget report.
     */
    public function exportCsv(int $userId, int $year, int $month): string
    {
        $review = $this->monthlyReview($userId, $year, $month);

        $lines = [];
        $lines[] = "Rapport budgétaire - {$review['monthLabel']}";
        $lines[] = '';
        $lines[] = "Revenus,{$review['income']}";
        $lines[] = "Dépenses,{$review['expense']}";
        $lines[] = "Balance,{$review['balance']}";
        $lines[] = "Taux d'épargne,{$review['savingsRate']}%";
        $lines[] = '';

        if ($review['previousMonth']) {
            $p = $review['previousMonth'];
            $lines[] = "Mois précédent:,,,,";
            $lines[] = "Revenus (M-1),{$p['income']}";
            $lines[] = "Dépenses (M-1),{$p['expense']}";
            $lines[] = "Taux épargne (M-1),{$p['savingsRate']}%";
            $lines[] = '';
        }

        $lines[] = 'Catégorie,Budget,Dépensé,Restant,% Utilisé,Statut';
        foreach ($review['categories'] as $cat) {
            $lines[] = implode(',', [
                $cat['category_name'],
                number_format($cat['limit'], 2),
                number_format($cat['spent'], 2),
                number_format($cat['remaining'], 2),
                $cat['percentage'] . '%',
                $cat['status'] === 'overspent' ? 'DÉPASSÉ' : ($cat['status'] === 'warning' ? 'ALERTE' : 'OK'),
            ]);
        }

        return implode("\n", $lines);
    }
}
