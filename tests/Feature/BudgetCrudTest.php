<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BudgetCrudTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $overrides));
    }

    /* ---------------- Guests ---------------- */

    public function test_guest_redirected_to_login_on_budgets_index(): void
    {
        $this->get(route('budgets.index'))
            ->assertRedirect(route('login'));
    }

    /* ---------------- Create ---------------- */

    public function test_user_can_create_budget_and_period_is_normalized(): void
    {
        $me = $this->verifiedUser();
        $cat = Category::create(['user_id' => $me->id, 'name' => 'Food']);

        $month = Carbon::now()->format('Y-m'); // e.g., "2025-09"

        $this->actingAs($me)->post(route('budgets.store'), [
            'category_id' => $cat->id,
            'period'      => $month,       // send Y-m from <input type="month">
            'amount'      => 55.00,
        ])->assertRedirect(route('budgets.index'));

        // Stored as first day of month (Y-m-01)
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $me->id,
            'category_id' => $cat->id,
            'period'      => \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)
                ->startOfMonth()
                ->toDateTimeString(), // "YYYY-MM-DD 00:00:00"
            'amount'      => 55.00,
        ]);
    }

    public function test_user_cannot_create_budget_with_others_category(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        $othersCat = Category::create(['user_id' => $them->id, 'name' => 'Their Cat']);

        $this->actingAs($me)->post(route('budgets.store'), [
            'category_id' => $othersCat->id,
            'period'      => now()->format('Y-m'),
            'amount'      => 10,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_same_month_same_category_is_unique_per_user(): void
    {
        $me  = $this->verifiedUser();
        $cat = Category::create(['user_id' => $me->id, 'name' => 'Transport']);

        $month = now()->format('Y-m');
        $this->actingAs($me)->post(route('budgets.store'), [
            'category_id' => $cat->id,
            'period'      => $month,
            'amount'      => 100,
        ])->assertRedirect();

        // Try duplicate
        $this->actingAs($me)->post(route('budgets.store'), [
            'category_id' => $cat->id,
            'period'      => $month,
            'amount'      => 80,
        ])->assertSessionHasErrors('period');
    }

    public function test_two_users_can_set_budgets_for_same_month_and_category_name(): void
    {
        $a = $this->verifiedUser(['email' => 'a@example.com']);
        $b = $this->verifiedUser(['email' => 'b@example.com']);

        // Same name but different owners => different category ids
        $catA = Category::create(['user_id' => $a->id, 'name' => 'Rent']);
        $catB = Category::create(['user_id' => $b->id, 'name' => 'Rent']);

        $month = now()->format('Y-m');

        $this->actingAs($a)->post(route('budgets.store'), [
            'category_id' => $catA->id, 'period' => $month, 'amount' => 300,
        ])->assertRedirect();

        $this->actingAs($b)->post(route('budgets.store'), [
            'category_id' => $catB->id, 'period' => $month, 'amount' => 450,
        ])->assertRedirect();

        $this->assertDatabaseCount('budgets', 2);
    }

    /* ---------------- Read / Index ---------------- */

    public function test_index_shows_only_my_budgets(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        $myCat    = Category::create(['user_id' => $me->id, 'name' => 'MineCat']);
        $theirCat = Category::create(['user_id' => $them->id, 'name' => 'TheirCat']);

        Budget::create([
            'user_id' => $me->id, 'category_id' => $myCat->id,
            'period'  => now()->startOfMonth()->toDateString(), 'amount' => 123,
        ]);
        Budget::create([
            'user_id' => $them->id, 'category_id' => $theirCat->id,
            'period'  => now()->startOfMonth()->toDateString(), 'amount' => 999,
        ]);

        $resp = $this->actingAs($me)->get(route('budgets.index'));
        $resp->assertOk();
        $resp->assertSee('MineCat');
        $resp->assertDontSee('TheirCat');
    }

    /* ---------------- Update ---------------- */

    public function test_user_can_update_own_budget(): void
    {
        $me  = $this->verifiedUser();
        $cat = Category::create(['user_id' => $me->id, 'name' => 'Bills']);

        $budget = Budget::create([
            'user_id' => $me->id,
            'category_id' => $cat->id,
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 200,
        ]);

        $newMonth = now()->addMonth()->format('Y-m'); // send Y-m

        $this->actingAs($me)->put(route('budgets.update', $budget), [
            'category_id' => $cat->id,
            'period'      => $newMonth,
            'amount'      => 250,
        ])->assertRedirect(route('budgets.index'));

        $this->assertDatabaseHas('budgets', [
            'id'      => $budget->id,
            'amount'  => 250.00,
            'period'  => \Illuminate\Support\Carbon::createFromFormat('Y-m', $newMonth)
                ->startOfMonth()
                ->toDateTimeString(),
        ]);
    }

    public function test_user_cannot_update_others_budget(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        $cat = Category::create(['user_id' => $them->id, 'name' => 'Secret']);
        $others = Budget::create([
            'user_id' => $them->id,
            'category_id' => $cat->id,
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 400,
        ]);

        $this->actingAs($me)->put(route('budgets.update', $others), [
            'category_id' => $cat->id,
            'period'      => now()->format('Y-m'),
            'amount'      => 1,
        ])->assertForbidden();

        $this->assertDatabaseHas('budgets', ['id' => $others->id, 'amount' => 400]);
    }

    /* ---------------- Delete ---------------- */

    public function test_user_can_delete_own_budget(): void
    {
        $me  = $this->verifiedUser();
        $cat = Category::create(['user_id' => $me->id, 'name' => 'Trash']);

        $budget = Budget::create([
            'user_id' => $me->id,
            'category_id' => $cat->id,
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 50,
        ]);

        $this->actingAs($me)->delete(route('budgets.destroy', $budget))
            ->assertRedirect(route('budgets.index'));

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_user_cannot_delete_others_budget(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);
        $cat  = Category::create(['user_id' => $them->id, 'name' => 'Keep']);

        $others = Budget::create([
            'user_id' => $them->id,
            'category_id' => $cat->id,
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 77,
        ]);

        $this->actingAs($me)->delete(route('budgets.destroy', $others))
            ->assertForbidden();

        $this->assertDatabaseHas('budgets', ['id' => $others->id, 'amount' => 77]);
    }
}
