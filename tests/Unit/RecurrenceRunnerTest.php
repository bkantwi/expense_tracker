<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Recurrence;
use App\Models\User;
use App\Services\RecurrenceRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecurrenceRunnerTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    public function test_materializes_and_advances_next_run(): void
    {
        $u = $this->user();
        $cat = Category::create(['user_id'=>$u->id,'name'=>'Rent']);

        $rec = Recurrence::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'House Rent',
            'amount'=>500,'cadence'=>'monthly','interval'=>1,'next_run_on'=>'2025-09-01','active'=>true,
        ]);

        $out = app(RecurrenceRunner::class)->runDue(Carbon::parse('2025-09-10'));

        $this->assertSame(1, $out['created']);

        $this->assertDatabaseHas('expenses', [
            'user_id'     => $u->id,
            'category_id' => $cat->id,
            'title'       => 'House Rent',
            'amount'      => 500,
            'spent_at'    => Carbon::parse('2025-09-01')->startOfDay()->toDateTimeString(), // "YYYY-MM-DD 00:00:00"
        ]);

        $rec->refresh();
        $this->assertEquals('2025-10-01', $rec->next_run_on->toDateString());
        $this->assertEquals('2025-09-01', $rec->last_run_on->toDateString());
    }

    public function test_idempotent_if_run_twice(): void
    {
        $u = $this->user();
        $cat = Category::create(['user_id'=>$u->id,'name'=>'Data']);

        Recurrence::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'Bundle','amount'=>50,
            'cadence'=>'weekly','interval'=>2,'next_run_on'=>'2025-09-12','active'=>true,
        ]);

        $svc = app(RecurrenceRunner::class);
        $svc->runDue(Carbon::parse('2025-09-12'));
        $svc->runDue(Carbon::parse('2025-09-12'));

        $this->assertEquals(1, Expense::count());
    }
}
