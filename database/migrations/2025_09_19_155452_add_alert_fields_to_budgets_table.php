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
        Schema::table('budgets', function (Blueprint $table) {
            $table->boolean('alerts_enabled')->default(true);
            $table->unsignedTinyInteger('warn_threshold')->default(80);
            $table->unsignedTinyInteger('at_threshold')->default(100);
            $table->unsignedTinyInteger('over_threshold')->default(110);

            $table->timestamp('warn_sent_at')->nullable();
            $table->timestamp('at_sent_at')->nullable();
            $table->timestamp('over_sent_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'period']);
            $table->dropColumn([
                'alerts_enabled','warn_threshold','at_threshold','over_threshold',
                'warn_sent_at','at_sent_at','over_sent_at',
            ]);
        });
    }
};
