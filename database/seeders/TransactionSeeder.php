<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'martin@fournier.dev')->first()
            ?? User::factory()->create([
                'name' => 'Martin Fournier',
                'email' => 'martin@fournier.dev',
            ]);

        $categories = Category::where('user_id', $user->id)->get()->keyBy('name');

        // === BUDGETS (current month + 2 previous) ===
        $budgetDefs = [
            'Loyer'             => 1100,
            'Épicerie'          => 500,
            'Restaurant'        => 200,
            'Transport'         => 150,
            'Assurances'        => 120,
            'Électricité'       => 80,
            'Internet/Téléphone' => 90,
            'Loisirs'           => 150,
            'Santé'             => 60,
            'Vêtements'         => 100,
            'Éducation'         => 80,
            'Abonnements'       => 50,
            'Divers'            => 100,
        ];

        $now = Carbon::now();
        foreach ([0, 1, 2] as $monthsAgo) {
            $month = $now->copy()->subMonths($monthsAgo);
            $monthStr = $month->format('Y-m');

            foreach ($budgetDefs as $catName => $limit) {
                $cat = $categories->get($catName);
                if (!$cat) continue;
                Budget::updateOrCreate(
                    ['user_id' => $user->id, 'category_id' => $cat->id, 'month' => $monthStr],
                    ['limit_amount' => $limit]
                );
            }
        }

        $this->command->info('✅ Budgets seeded (3 months)');

        // === SALAIRE (monthly income) ===
        foreach ([2, 1, 0] as $monthsAgo) {
            $month = $now->copy()->subMonths($monthsAgo);
            $payDay = $month->copy()->day(1)->addDays(14); // ~15th
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $categories['Salaire']->id,
                'amount' => 4200,
                'type' => 'income',
                'description' => 'Salaire mensuel',
                'date' => $payDay,
            ]);
        }

        // === MONTHLY BILLS (recurring) ===
        foreach ([2, 1, 0] as $monthsAgo) {
            $m = $now->copy()->subMonths($monthsAgo);

            $bills = [
                ['Loyer', 1025, 'Loyer appartement', $m->copy()->day(1)],
                ['Assurances', 115, 'Assurance habitation', $m->copy()->day(5)],
                ['Internet/Téléphone', 85, 'Bell Internet + mobile', $m->copy()->day(10)],
                ['Électricité', 72, 'Hydro‑Québec', $m->copy()->day(15)],
                ['Abonnements', 14.99, 'Netflix', $m->copy()->day(20)],
                ['Abonnements', 9.99, 'Spotify', $m->copy()->day(22)],
            ];

            foreach ($bills as [$catName, $amount, $desc, $date]) {
                $cat = $categories->get($catName);
                if (!$cat) continue;
                Transaction::create([
                    'user_id' => $user->id,
                    'category_id' => $cat->id,
                    'amount' => $amount,
                    'type' => 'expense',
                    'description' => $desc,
                    'date' => $date,
                    'is_recurring' => true,
                ]);
            }
        }

        // === VARIABLE EXPENSES (random realistic data) ===
        $variableExpenses = [
            ['Épicerie',  85.50, 'Maxi courses', 3],
            ['Épicerie', 112.30, 'Super C', 8],
            ['Épicerie',  67.80, 'IGA', 14],
            ['Épicerie',  95.20, 'Costco', 19],
            ['Épicerie',  78.40, 'Marché local', 25],
            ['Restaurant', 42.00, 'Sushi St‑Hubert', 5],
            ['Restaurant', 28.50, 'Café matinal', 12],
            ['Restaurant', 56.80, 'Pizzeria', 18],
            ['Transport',  45.00, 'Essence', 7],
            ['Transport',  48.50, 'Essence', 21],
            ['Transport',   3.50, 'STM ticket', 10],
            ['Transport',  14.00, 'Uber', 16],
            ['Loisirs',    35.00, 'Cinéma', 9],
            ['Loisirs',    22.00, 'Jeux vidéo', 16],
            ['Loisirs',    60.00, 'Sortie entre amis', 23],
            ['Santé',      28.00, 'Pharmacie', 11],
            ['Santé',      32.00, 'Suppléments', 22],
            ['Vêtements',  75.00, 'Jean UNIQLO', 13],
            ['Éducation',  45.00, 'Livre technique', 8],
            ['Divers',     15.00, 'Dollarama', 6],
            ['Divers',     22.50, 'Amazon', 17],
        ];

        foreach ([2, 1, 0] as $monthsAgo) {
            $month = $now->copy()->subMonths($monthsAgo);
            $daysInMonth = $month->daysInMonth;

            foreach ($variableExpenses as [$catName, $amount, $desc, $day]) {
                // Jitter day within month
                $actualDay = min(max(1, $day + random_int(-2, 2)), $daysInMonth);
                $cat = $categories->get($catName);
                if (!$cat) continue;

                Transaction::create([
                    'user_id' => $user->id,
                    'category_id' => $cat->id,
                    'amount' => round($amount + random_int(-10, 10), 2),
                    'type' => 'expense',
                    'description' => $desc,
                    'date' => $month->copy()->day($actualDay),
                ]);
            }
        }

        // Extra large expense in one month (to show overspend)
        $twoMonthsAgo = $now->copy()->subMonths(2);
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categories['Restaurant']->id,
            'amount' => 185.40,
            'type' => 'expense',
            'description' => 'Souper anniversaire',
            'date' => $twoMonthsAgo->copy()->day(20),
        ]);

        // Small freelance income
        foreach ([1, 0] as $monthsAgo) {
            $month = $now->copy()->subMonths($monthsAgo);
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $categories['Freelance']->id,
                'amount' => random_int(1, 3) * 500,
                'type' => 'income',
                'description' => 'Projet freelance Web',
                'date' => $month->copy()->day(random_int(20, 28)),
            ]);
        }

        // Une dépense > 80% budget dans le mois courant pour tester l'alerte
        $currentMonthExpensiveRestaurant = $now->copy()->day(min(15, $now->daysInMonth));
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categories['Restaurant']->id,
            'amount' => 165.00,
            'type' => 'expense',
            'description' => 'Brunch dominical (familial)',
            'date' => $currentMonthExpensiveRestaurant,
        ]);

        $this->command->info('✅ Transactions seeded (3 months of data)');
    }
}
