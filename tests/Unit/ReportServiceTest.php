<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    /** @test */
    public function totals_by_month_groups_and_sums_correctly(): void
    {
        $user = $this->makeUser();
        $food = Category::create(['user_id' => $user->id, 'name' => 'Food']);
        $transport = Category::create(['user_id' => $user->id, 'name' => 'Transport']);

        // Jan 2025
        Expense::create(['user_id'=>$user->id,'category_id'=>$food->id,'title'=>'Groceries','amount'=>50,'spent_at'=>'2025-01-10']);
        Expense::create(['user_id'=>$user->id,'category_id'=>$transport->id,'title'=>'Bus','amount'=>100,'spent_at'=>'2025-01-15']);
        // Feb 2025
        Expense::create(['user_id'=>$user->id,'category_id'=>$food->id,'title'=>'Eating out','amount'=>200,'spent_at'=>'2025-02-05']);

        $svc = app(ReportService::class);
        $out = $svc->totalsByMonth($user->id, Carbon::parse('2025-01-01'), Carbon::parse('2025-02-28'));

        $this->assertSame([
            ['month' => '2025-01', 'total' => 150.0],
            ['month' => '2025-02', 'total' => 200.0],
        ], $out->toArray());
    }

    /** @test */
    public function totals_by_category_groups_within_range(): void
    {
        $user = $this->makeUser();
        $food = Category::create(['user_id'=>$user->id,'name'=>'Food']);
        $transport = Category::create(['user_id'=>$user->id,'name'=>'Transport']);

        Expense::create(['user_id'=>$user->id,'category_id'=>$food->id,'title'=>'Groceries','amount'=>123.45,'spent_at'=>'2025-03-01']);
        Expense::create(['user_id'=>$user->id,'category_id'=>$food->id,'title'=>'Lunch','amount'=>76.55,'spent_at'=>'2025-03-10']); // total 200.00
        Expense::create(['user_id'=>$user->id,'category_id'=>$transport->id,'title'=>'Taxi','amount'=>80,'spent_at'=>'2025-03-15']);

        $svc = app(ReportService::class);
        $out = $svc->totalsByCategory($user->id, Carbon::parse('2025-03-01'), Carbon::parse('2025-03-31'));

        // Sorted desc by total (Food first)
        $this->assertSame('Food', $out[0]['category']);
        $this->assertSame(200.0, $out[0]['total']);
        $this->assertSame('Transport', $out[1]['category']);
        $this->assertSame(80.0, $out[1]['total']);
    }

    /** @test */
    public function budget_vs_actual_matches_month(): void
    {
        $user = $this->makeUser();
        $food = Category::create(['user_id'=>$user->id,'name'=>'Food']);
        $transport = Category::create(['user_id'=>$user->id,'name'=>'Transport']);

        // Budgets for Feb 2025
        Budget::create(['user_id'=>$user->id,'category_id'=>$food->id,'period'=>'2025-02-01','amount'=>180]);
        Budget::create(['user_id'=>$user->id,'category_id'=>$transport->id,'period'=>'2025-02-01','amount'=>50]);

        // Expenses in Feb
        Expense::create(['user_id'=>$user->id,'category_id'=>$food->id,'title'=>'Dinner','amount'=>120,'spent_at'=>'2025-02-03']);
        Expense::create(['user_id'=>$user->id,'category_id'=>$food->id,'title'=>'Lunch','amount'=>80,'spent_at'=>'2025-02-12']); // Food total: 200

        $svc = app(ReportService::class);
        $out = collect($svc->budgetVsActual($user->id, Carbon::parse('2025-02-10')))
            ->keyBy('category');

        $this->assertSame(180.0, $out['Food']['budget']);
        $this->assertSame(200.0, $out['Food']['spent']);
        $this->assertSame(-20.0, $out['Food']['variance']);

        $this->assertSame(50.0, $out['Transport']['budget']);
        $this->assertSame(0.0, $out['Transport']['spent']);
        $this->assertSame(50.0, $out['Transport']['variance']);
    }
}
