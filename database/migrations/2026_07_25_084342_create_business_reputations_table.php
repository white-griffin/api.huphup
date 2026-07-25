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
        Schema::create('business_reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('rating_avg', 3, 2)
                ->default(0);

            $table->unsignedInteger('rating_count')
                ->default(0);

            $table->unsignedInteger('review_count')
                ->default(0);

            $table->decimal('reputation_score', 5, 2)
                ->default(0);

            $table->decimal('current_commission_rate', 5, 2)
                ->default(0);

            $table->timestamp('last_calculated_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_reputations');
    }
};
