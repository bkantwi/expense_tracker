<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Services\BudgetAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BudgetAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User { return User::factory()->create(['email_verified_at'=>now()]); }

    public function test_sends_each_threshold_once_and_in_order(): void
    {
        Notification::fake();

        $u = $this->user();
        $cat = Category::create(['user_id'=>$u->id,'name'=>'Food']);
        $budget = Budget::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'period'=>'2025-09-01','amount'=>100,
            'alerts_enabled'=>true,'warn_threshold'=>80,'at_threshold'=>100,'over_threshold'=>110,
        ]);

        $svc = app(BudgetAlertService::class);

        // 1) 85% -> warn
        Expense::create(['user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'A','amount'=>85,'spent_at'=>'2025-09-05']);
        $out = $svc->check(Carbon::parse('2025-09-10'));
        $this->assertSame(1, $out['notified']);
        $budget->refresh();
        $this->assertNotNull($budget->warn_sent_at);
        Notification::assertSentTo($u, \App\Notifications\BudgetThresholdCrossed::class, function($n) {
            return $n->level === 'warn';
        });

        // Re-run: no duplicate
        Notification::fake();
        $svc->check(Carbon::parse('2025-09-11'));
        Notification::assertNothingSent();

        // 2) reach 100% -> 'at'
        Expense::create(['user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'B','amount'=>15,'spent_at'=>'2025-09-12']); // now 100
        Notification::fake();
        $svc->check(Carbon::parse('2025-09-13'));
        $budget->refresh();
        $this->assertNotNull($budget->at_sent_at);
        Notification::assertSentTo($u, \App\Notifications\BudgetThresholdCrossed::class, fn($n) => $n->level==='at');

        // 3) over 110% -> 'over'
        Expense::create(['user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'C','amount'=>20,'spent_at'=>'2025-09-15']); // 120
        Notification::fake();
        $svc->check(Carbon::parse('2025-09-16'));
        $budget->refresh();
        $this->assertNotNull($budget->over_sent_at);
        Notification::assertSentTo($u, \App\Notifications\BudgetThresholdCrossed::class, fn($n) => $n->level==='over');
    }

    public function test_respects_disabled_alerts(): void
    {
        Notification::fake();

        $u = $this->user();
        $cat = Category::create(['user_id'=>$u->id,'name'=>'Transport']);
        Budget::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'period'=>'2025-09-01','amount'=>100,
            'alerts_enabled'=>false,
        ]);
        Expense::create(['user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'Taxi','amount'=>200,'spent_at'=>'2025-09-10']);

        app(BudgetAlertService::class)->check(Carbon::parse('2025-09-20'));

        Notification::assertNothingSent();
    }
}
