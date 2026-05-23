<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'martin@fournier.dev')->first()
            ?? User::factory()->create([
                'name' => 'Martin Fournier',
                'email' => 'martin@fournier.dev',
            ]);

        $categories = [
            // Income
            ['name' => 'Salaire',           'type' => 'income',  'icon' => '💼', 'color' => '#10b981'],
            ['name' => 'Freelance',          'type' => 'income',  'icon' => '💻', 'color' => '#3b82f6'],
            ['name' => 'Revenus locatifs',   'type' => 'income',  'icon' => '🏠', 'color' => '#8b5cf6'],
            ['name' => 'Dividendes',         'type' => 'income',  'icon' => '📈', 'color' => '#06b6d4'],
            ['name' => 'Autres revenus',     'type' => 'income',  'icon' => '💰', 'color' => '#6b7280'],

            // Expenses
            ['name' => 'Loyer',             'type' => 'expense', 'icon' => '🏠', 'color' => '#ef4444'],
            ['name' => 'Épicerie',           'type' => 'expense', 'icon' => '🛒', 'color' => '#f59e0b'],
            ['name' => 'Restaurant',         'type' => 'expense', 'icon' => '🍽️', 'color' => '#f97316'],
            ['name' => 'Transport',          'type' => 'expense', 'icon' => '🚗', 'color' => '#6366f1'],
            ['name' => 'Assurances',         'type' => 'expense', 'icon' => '🛡️', 'color' => '#a855f7'],
            ['name' => 'Électricité',        'type' => 'expense', 'icon' => '⚡', 'color' => '#eab308'],
            ['name' => 'Internet/Téléphone', 'type' => 'expense', 'icon' => '📱', 'color' => '#14b8a6'],
            ['name' => 'Loisirs',            'type' => 'expense', 'icon' => '🎮', 'color' => '#ec4899'],
            ['name' => 'Santé',              'type' => 'expense', 'icon' => '💊', 'color' => '#22c55e'],
            ['name' => 'Vêtements',          'type' => 'expense', 'icon' => '👕', 'color' => '#8b5cf6'],
            ['name' => 'Éducation',          'type' => 'expense', 'icon' => '📚', 'color' => '#06b6d4'],
            ['name' => 'Abonnements',        'type' => 'expense', 'icon' => '📋', 'color' => '#64748b'],
            ['name' => 'Divers',             'type' => 'expense', 'icon' => '📦', 'color' => '#78716c'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $cat['name'], 'type' => $cat['type']],
                ['icon' => $cat['icon'], 'color' => $cat['color']]
            );
        }

        $this->command->info('✅ Categories seeded for user: ' . $user->email);
    }
}
