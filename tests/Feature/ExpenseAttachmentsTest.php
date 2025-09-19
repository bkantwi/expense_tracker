<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User { return User::factory()->create(['email_verified_at'=>now()]); }

    public function test_user_can_upload_and_delete_attachments(): void
    {
        Storage::fake('public');

        $u = $this->user();
        $cat = \App\Models\Category::create(['user_id'=>$u->id,'name'=>'Food']);
        $exp = Expense::create([
            'user_id'=>$u->id,'category_id'=>$cat->id,'title'=>'Lunch','amount'=>10,'spent_at'=>'2025-09-10'
        ]);

        // upload two files
        $res = $this->actingAs($u)->post(route('expenses.attachments.store', $exp), [
            'files' => [
                UploadedFile::fake()->image('receipt.jpg', 600, 600),
                UploadedFile::fake()->create('note.pdf', 20, 'application/pdf'),
            ]
        ]);
        $res->assertRedirect();
        $this->assertDatabaseCount('expense_attachments', 2);

        // delete one
        $attId = \App\Models\ExpenseAttachment::first()->id;
        $this->actingAs($u)->delete(route('attachments.destroy', $attId))
            ->assertRedirect();
        $this->assertDatabaseCount('expense_attachments', 1);
    }

    public function test_cannot_upload_to_someone_elses_expense(): void
    {
        Storage::fake('public');

        $me = $this->user();
        $other = $this->user();

        $cat = \App\Models\Category::create(['user_id'=>$other->id,'name'=>'Other']);
        $exp = Expense::create(['user_id'=>$other->id,'category_id'=>$cat->id,'title'=>'X','amount'=>1,'spent_at'=>'2025-09-10']);

        $this->actingAs($me)->post(route('expenses.attachments.store', $exp), [
            'files' => [UploadedFile::fake()->image('hack.jpg')],
        ])->assertForbidden(); // relies on ExpensePolicy@update
    }
}
