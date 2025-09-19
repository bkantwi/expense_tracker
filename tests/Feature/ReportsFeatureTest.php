<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function user(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    /** @test */
    public function index_shows_monthly_and_category_tables(): void
    {
        $me = $this->user();
        $food = Category::create(['user_id'=>$me->id,'name'=>'Food']);
        $transport = Category::create(['user_id'=>$me->id,'name'=>'Transport']);

        // Two months window: Jan–Feb 2025
        Expense::create(['user_id'=>$me->id,'category_id'=>$food->id,'title'=>'Groceries','amount'=>150.00,'spent_at'=>'2025-01-10']);
        Expense::create(['user_id'=>$me->id,'category_id'=>$food->id,'title'=>'Eating out','amount'=>200.00,'spent_at'=>'2025-02-05']);
        Expense::create(['user_id'=>$me->id,'category_id'=>$transport->id,'title'=>'Bus','amount'=>80.00,'spent_at'=>'2025-02-10']);

        $res = $this->actingAs($me)->get(route('reports.index', [
            'start' => '2025-01-01',
            'end'   => '2025-02-28',
        ]));

        $res->assertOk();

        // Monthly table shows months & totals (assert numbers to avoid currency symbol encoding issues)
        $res->assertSee('2025-01');
        $res->assertSee('2025-02');
        $res->assertSee('150.00'); // Jan total
        $res->assertSee('280.00'); // Feb total (200 + 80)

        // Category table shows totals across the whole range
        $res->assertSee('Food');
        $res->assertSee('Transport');
        $res->assertSee('350.00'); // Food (150 + 200)
        $res->assertSee('80.00');  // Transport
    }

    /** @test */
    public function index_shows_budget_vs_actual_when_full_single_month_selected(): void
    {
        $me = $this->user();
        $food = Category::create(['user_id'=>$me->id,'name'=>'Food']);
        $transport = Category::create(['user_id'=>$me->id,'name'=>'Transport']);

        // Budgets for March 2025
        Budget::create(['user_id'=>$me->id,'category_id'=>$food->id,'period'=>'2025-03-01','amount'=>300]);
        Budget::create(['user_id'=>$me->id,'category_id'=>$transport->id,'period'=>'2025-03-01','amount'=>90]);

        // Expenses in March
        Expense::create(['user_id'=>$me->id,'category_id'=>$food->id,'title'=>'Groceries','amount'=>120,'spent_at'=>'2025-03-02']);
        Expense::create(['user_id'=>$me->id,'category_id'=>$food->id,'title'=>'Dining','amount'=>150,'spent_at'=>'2025-03-18']); // Food 270
        Expense::create(['user_id'=>$me->id,'category_id'=>$transport->id,'title'=>'Bus','amount'=>100,'spent_at'=>'2025-03-22']); // Transport 100

        $start = Carbon::parse('2025-03-01')->toDateString();
        $end   = Carbon::parse('2025-03-31')->toDateString();

        $res = $this->actingAs($me)->get(route('reports.index', compact('start','end')));

        $res->assertOk();
        $res->assertSee('Budget vs Actual — Mar 2025');

        // Shows categories and the spent/budget numbers
        $res->assertSee('Food');
        $res->assertSee('270.00'); // spent
        $res->assertSee('300.00'); // budget

        $res->assertSee('Transport');
        $res->assertSee('100.00'); // spent
        $res->assertSee('90.00');  // budget
    }

    /** @test */
    public function export_downloads_csv_for_range(): void
    {
        $me = $this->user();
        $food = \App\Models\Category::create(['user_id'=>$me->id,'name'=>'Food']);

        \App\Models\Expense::create([
            'user_id'=>$me->id,'category_id'=>$food->id,'title'=>'Groceries',
            'amount'=>55.25,'spent_at'=>'2025-01-10','notes'=>'milk,bread'
        ]);

        $res = $this->actingAs($me)->get(route('reports.export', [
            'start' => '2025-01-01',
            'end'   => '2025-01-31',
        ]));

        // Status & headers
        $res->assertOk();
        $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // Optional: assert the download file name (from streamDownload)
        $res->assertDownload('expenses_20250101_20250131.csv');

        // 🔑 Read the streamed body, then assert contents
        $csv = $res->streamedContent();

        $this->assertStringContainsString("Date,Title,Category,Amount,Notes", $csv);
        // Just match the prefix of the row; notes may be quoted, and line endings may differ
        $this->assertStringContainsString("2025-01-10,Groceries,Food,55.25", $csv);
    }
}
