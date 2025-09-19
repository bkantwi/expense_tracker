<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCrudTest extends TestCase
{
    use RefreshDatabase;

    private function me(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get(route('expenses.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_expense_with_own_category(): void
    {
        $me = $this->me();
        $cat = Category::create(['user_id'=>$me->id,'name'=>'Food']);

        $this->actingAs($me)->post(route('expenses.store'), [
            'title' => 'Lunch',
            'amount'=> 25.50,
            'spent_at' => now()->toDateString(),
            'category_id' => $cat->id,
            'notes' => 'Chicken & rice',
        ])->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'user_id' => $me->id,
            'category_id' => $cat->id,
            'title' => 'Lunch',
        ]);
    }

    public function test_user_cannot_use_someone_elses_category(): void
    {
        $me   = $this->me();
        $them = User::factory()->create(['email_verified_at'=>now()]);
        $othersCat = Category::create(['user_id'=>$them->id,'name'=>'Hack']);

        $this->actingAs($me)->post(route('expenses.store'), [
            'title' => 'Bad',
            'amount'=> 10,
            'spent_at' => now()->toDateString(),
            'category_id' => $othersCat->id,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_user_can_update_and_delete_their_expense(): void
    {
        $me = $this->me();
        $cat = Category::create(['user_id'=>$me->id,'name'=>'Transport']);
        $exp = Expense::create([
            'user_id'=>$me->id,'category_id'=>$cat->id,'title'=>'Uber','amount'=>30,'spent_at'=>now()->toDateString()
        ]);

        $this->actingAs($me)->put(route('expenses.update',$exp), [
            'title'=>'Bolt','amount'=>28.75,'spent_at'=>now()->toDateString(),'category_id'=>$cat->id
        ])->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses',['id'=>$exp->id,'title'=>'Bolt']);

        $this->actingAs($me)->delete(route('expenses.destroy',$exp))
            ->assertRedirect(route('expenses.index'));

        $this->assertDatabaseMissing('expenses',['id'=>$exp->id]);
    }
}
