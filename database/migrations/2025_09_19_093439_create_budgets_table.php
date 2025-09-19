<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            // Each budget belongs to a user and a category
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            // Period = first day of the month (e.g., 2025-09-01)
            $table->date('period');

            $table->decimal('amount', 12, 2);

            // A user can have only one budget per category per month
            $table->unique(['user_id', 'category_id', 'period']);

            $table->timestamps();

            // Helpful index for queries
            $table->index(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
