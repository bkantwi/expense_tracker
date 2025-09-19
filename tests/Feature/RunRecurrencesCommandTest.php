<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recurrence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RunRecurrencesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_expenses_for_due_recurrences(): void
    {
        $u = User::factory()->create(['email_verified_at' => now()]);
        $cat = Category::create(['user_id'=>$u->id,'name'=>'Subscription']);

        Recurrence::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'Netflix','amount'=>45,
            'cadence'=>'monthly','interval'=>1,'next_run_on'=>'2025-09-01','active'=>true,
        ]);

        $this->artisan('expenses:run-recurrences --date=2025-09-05')
            ->expectsOutputToContain('Created: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('expenses', [
            'title'    => 'Netflix',
            'amount'   => 45,
            'spent_at' => Carbon::parse('2025-09-01')->startOfDay()->toDateTimeString(),
        ]);
    }
}
