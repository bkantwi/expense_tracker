<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->decimal('amount', 12, 2);

            // cadence + interval => e.g., WEEKLY every 2 weeks, MONTHLY every 3 months (quarterly)
            $table->string('cadence');            // daily, weekly, monthly, quarterly, yearly
            $table->unsignedInteger('interval')->default(1);

            $table->date('next_run_on');
            $table->date('last_run_on')->nullable();
            $table->date('ends_on')->nullable();

            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'next_run_on']);
            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurrences');
    }
};
