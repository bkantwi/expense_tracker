<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $overrides));
    }

    /* ---------- Guests ---------- */

    public function test_guest_is_redirected_to_login_on_index(): void
    {
        $this->get(route('categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_create(): void
    {
        $this->get(route('categories.create'))
            ->assertRedirect(route('login'));
    }

    /* ---------- Create ---------- */

    public function test_user_can_create_category(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->post(route('categories.store'), [
                'name' => 'Food',
                'description' => 'All food-related expenses',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name'    => 'Food',
        ]);
    }

    public function test_name_must_be_unique_per_user(): void
    {
        $user = $this->verifiedUser();

        // Seed one category for this user
        Category::create([
            'user_id'     => $user->id,
            'name'        => 'Transport',
            'description' => null,
        ]);

        $this->actingAs($user)
            ->post(route('categories.store'), [
                'name' => 'Transport',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_two_different_users_may_use_same_name(): void
    {
        $userA = $this->verifiedUser(['email' => 'a@example.com']);
        $userB = $this->verifiedUser(['email' => 'b@example.com']);

        // User A creates "Rent"
        $this->actingAs($userA)
            ->post(route('categories.store'), ['name' => 'Rent'])
            ->assertRedirect();

        // User B can also create "Rent"
        $this->actingAs($userB)
            ->post(route('categories.store'), ['name' => 'Rent'])
            ->assertRedirect();

        $this->assertDatabaseCount('categories', 2);
    }

    /* ---------- Read ---------- */

    public function test_user_sees_only_their_own_categories_on_index(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        Category::create(['user_id' => $me->id, 'name' => 'Mine',  'description' => null]);
        Category::create(['user_id' => $them->id, 'name' => 'Theirs','description' => null]);

        $response = $this->actingAs($me)->get(route('categories.index'));
        $response->assertOk();
        $response->assertSee('Mine');
        $response->assertDontSee('Theirs');
    }

    public function test_user_cannot_view_others_category(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        $others = Category::create(['user_id' => $them->id, 'name' => 'Secret', 'description' => null]);

        $this->actingAs($me)
            ->get(route('categories.show', $others))
            ->assertForbidden();
    }

    /* ---------- Update ---------- */

    public function test_user_can_update_own_category(): void
    {
        $user = $this->verifiedUser();
        $cat  = Category::create(['user_id' => $user->id, 'name' => 'Travel', 'description' => null]);

        $this->actingAs($user)
            ->put(route('categories.update', $cat), [
                'name' => 'Transport', // rename
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id'      => $cat->id,
            'user_id' => $user->id,
            'name'    => 'Transport',
        ]);
    }

    public function test_user_cannot_update_others_category(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        $others = Category::create(['user_id' => $them->id, 'name' => 'Utilities', 'description' => null]);

        $this->actingAs($me)
            ->put(route('categories.update', $others), ['name' => 'Hacked'])
            ->assertForbidden();

        $this->assertDatabaseHas('categories', [
            'id'   => $others->id,
            'name' => 'Utilities',
        ]);
    }

    /* ---------- Delete ---------- */

    public function test_user_can_delete_own_category(): void
    {
        $user = $this->verifiedUser();
        $cat  = Category::create(['user_id' => $user->id, 'name' => 'DeleteMe', 'description' => null]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $cat))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    public function test_user_cannot_delete_others_category(): void
    {
        $me   = $this->verifiedUser(['email' => 'me@example.com']);
        $them = $this->verifiedUser(['email' => 'them@example.com']);

        $others = Category::create(['user_id' => $them->id, 'name' => 'KeepMe', 'description' => null]);

        $this->actingAs($me)
            ->delete(route('categories.destroy', $others))
            ->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $others->id, 'name' => 'KeepMe']);
    }
}
