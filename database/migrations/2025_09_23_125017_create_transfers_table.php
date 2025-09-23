<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('from_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->constrained('accounts')->cascadeOnDelete();

            $table->date('transferred_at');
            $table->decimal('amount', 12, 2);
            $table->string('memo')->nullable();
            $table->timestamps();

            $table->index(
                ['user_id','from_account_id','to_account_id','transferred_at'],
                'transfers_user_from_to_date_idx'
            );

            // Optional: requires MySQL 8.0.16+ to enforce at DB level
//             $table->check('from_account_id <> to_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
