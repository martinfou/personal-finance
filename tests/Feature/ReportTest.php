<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Carbon $now;
    private string $monthStr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->now = Carbon::create(2026, 5, 15); // Fixed May 2026
        Carbon::setTestNow($this->now);
        $this->monthStr = '2026-05';

        // Create categories
        $categories = [
            ['name' => 'Salaire', 'type' => 'income', 'icon' => '💼', 'color' => '#10b981'],
            ['name' => 'Épicerie', 'type' => 'expense', 'icon' => '🛒', 'color' => '#f59e0b'],
            ['name' => 'Restaurant', 'type' => 'expense', 'icon' => '🍽️', 'color' => '#f97316'],
            ['name' => 'Loyer', 'type' => 'expense', 'icon' => '🏠', 'color' => '#ef4444'],
            ['name' => 'Loisirs', 'type' => 'expense', 'icon' => '🎮', 'color' => '#ec4899'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['user_id' => $this->user->id]));
        }

        // Create budgets for current month
        $budgets = [
            ['Épicerie' => 500],
            ['Restaurant' => 200],
            ['Loyer' => 1100],
            ['Loisirs' => 150],
        ];

        foreach ($budgets as $b) {
            $catName = array_key_first($b);
            $limit = $b[$catName];
            $cat = Category::where('user_id', $this->user->id)->where('name', $catName)->first();
            Budget::create([
                'user_id' => $this->user->id,
                'category_id' => $cat->id,
                'limit_amount' => $limit,
                'month' => $this->monthStr,
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createTransaction(string $categoryName, float $amount, string $type = 'expense', ?string $date = null): Transaction
    {
        $cat = Category::where('user_id', $this->user->id)->where('name', $categoryName)->first();
        return Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $cat->id,
            'amount' => $amount,
            'type' => $type,
            'description' => "Test {$categoryName}",
            'date' => $date ?? $this->now->copy()->format('Y-m-d'),
        ]);
    }

    public function test_report_index_page_shows_available_months(): void
    {
        // Create budgets for multiple months
        $this->createTransaction('Épicerie', 50);

        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->has('months')
        );
    }

    public function test_monthly_report_shows_budget_vs_actuals(): void
    {
        // Add income + expenses for realistic review
        $this->createTransaction('Salaire', 4200, 'income');
        $this->createTransaction('Loyer', 1025);
        $this->createTransaction('Épicerie', 350);
        $this->createTransaction('Restaurant', 85);
        $this->createTransaction('Loisirs', 45);

        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Monthly')
            ->has('income')
            ->has('expense')
            ->has('balance')
            ->has('savingsRate')
            ->has('categories', 4) // 4 budgeted categories
            ->has('alerts')
        );
    }

    public function test_monthly_report_calculates_savings_rate_correctly(): void
    {
        $this->createTransaction('Salaire', 5000, 'income');
        $this->createTransaction('Loyer', 1000);
        $this->createTransaction('Épicerie', 400);

        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertInertia(fn ($page) => $page
            ->where('income', 5000)
            ->where('expense', 1400)
            ->where('balance', 3600)
            ->where('savingsRate', 72) // 3600/5000 * 100
        );
    }

    public function test_overspent_category_triggers_danger_alert(): void
    {
        // Spend way over Restaurant budget of $200
        $this->createTransaction('Restaurant', 250);

        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertInertia(fn ($page) => $page
            ->has('alerts', 1)
        );

        $alerts = $this->getAlertsFromResponse($response);
        $this->assertEquals('danger', $alerts[0]['type']);
        $this->assertStringContainsString('Restaurant', $alerts[0]['message']);
    }

    public function test_approaching_budget_triggers_warning_alert(): void
    {
        // Spend $170 of $200 Restaurant budget (85%)
        $this->createTransaction('Restaurant', 170);

        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertInertia(fn ($page) => $page
            ->has('alerts', 1)
        );

        $alerts = $this->getAlertsFromResponse($response);
        $this->assertEquals('warning', $alerts[0]['type']);
    }

    public function test_on_track_category_has_no_alert(): void
    {
        // Spend $50 of $500 Epicerie budget (10%)
        $this->createTransaction('Épicerie', 50);

        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertInertia(fn ($page) => $page
            ->has('alerts', 0)
        );
    }

    public function test_csv_export_returns_download(): void
    {
        $this->createTransaction('Salaire', 4200, 'income');
        $this->createTransaction('Épicerie', 300);

        $response = $this->actingAs($this->user)->get(
            route('reports.export-csv', ['year' => 2026, 'month' => 5])
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="budget-report-2026-5.csv"');
        $response->assertSee('Rapport budgétaire');
        $response->assertSee('Catégorie,Budget,Dépensé');
    }

    public function test_no_spending_shows_empty_categories_with_zero_usage(): void
    {
        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertInertia(fn ($page) => $page
            ->has('categories', 4)
            ->has('alerts', 0)
        );
    }

    public function test_previous_month_comparison_appears_when_data_exists(): void
    {
        // Create budget for previous month
        $prevMonth = '2026-04';
        $epicerieCat = Category::where('user_id', $this->user->id)->where('name', 'Épicerie')->first();
        $loyerCat = Category::where('user_id', $this->user->id)->where('name', 'Loyer')->first();

        Budget::create([
            'user_id' => $this->user->id,
            'category_id' => $epicerieCat->id,
            'limit_amount' => 500,
            'month' => $prevMonth,
        ]);
        Budget::create([
            'user_id' => $this->user->id,
            'category_id' => $loyerCat->id,
            'limit_amount' => 1100,
            'month' => $prevMonth,
        ]);

        // Previous month: income 4000, expenses 1400
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => Category::where('user_id', $this->user->id)->where('name', 'Salaire')->first()->id,
            'amount' => 4000,
            'type' => 'income',
            'description' => 'Salaire avril',
            'date' => '2026-04-15',
        ]);
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $epicerieCat->id,
            'amount' => 400,
            'type' => 'expense',
            'description' => 'Courses avril',
            'date' => '2026-04-10',
        ]);

        // Current month: income 5000, expenses 500
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => Category::where('user_id', $this->user->id)->where('name', 'Salaire')->first()->id,
            'amount' => 5000,
            'type' => 'income',
            'description' => 'Salaire mai',
            'date' => '2026-05-15',
        ]);
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $epicerieCat->id,
            'amount' => 500,
            'type' => 'expense',
            'description' => 'Courses mai',
            'date' => '2026-05-10',
        ]);

        $response = $this->actingAs($this->user)->get(
            route('reports.monthly', ['year' => 2026, 'month' => 5])
        );

        $response->assertInertia(fn ($page) => $page
            ->has('previousMonth')
            ->where('previousMonth.income', 4000)
            ->where('previousMonth.expense', 400)
            ->where('previousMonth.savingsRate', 90)
        );
    }

    public function test_budget_report_service_available_months(): void
    {
        $service = app(BudgetReportService::class);

        // Only one month of budget data (from setUp)
        $months = $service->availableMonths($this->user->id);

        $this->assertCount(1, $months);
        $this->assertEquals(2026, $months[0]['year']);
        $this->assertEquals(5, $months[0]['month']);
    }

    public function test_report_requires_authentication(): void
    {
        $response = $this->get(route('reports.monthly'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Helper: extract alerts array from an Inertia test response.
     */
    private function getAlertsFromResponse($response): array
    {
        $props = $response->baseResponse->getOriginalContent()->getData()['page']['props'] ?? [];
        return $props['alerts'] ?? [];
    }
}
