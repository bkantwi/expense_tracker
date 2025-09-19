<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BudgetAlertsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_notifications_and_sets_flags(): void
    {
        Notification::fake();

        $u = User::factory()->create(['email_verified_at'=>now()]);
        $cat = Category::create(['user_id'=>$u->id,'name'=>'Data']);
        $b = Budget::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'period'=>'2025-09-01','amount'=>50,
            'alerts_enabled'=>true,'warn_threshold'=>80,'at_threshold'=>100,'over_threshold'=>110,
        ]);

        Expense::create(['user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'Bundle','amount'=>45,'spent_at'=>'2025-09-05']); // 90%

        $this->artisan('budgets:check-alerts --date=2025-09-10')
            ->expectsOutputToContain('Checked:')
            ->expectsOutputToContain('Notified:')
            ->assertExitCode(0);

        $b->refresh();
        $this->assertNotNull($b->warn_sent_at);
        Notification::assertSentTo($u, \App\Notifications\BudgetThresholdCrossed::class);
    }
}
