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
        Schema::create('review_summaries', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable');

            $table->unsignedInteger('reviews_count')->default(0);

            $table->unsignedInteger('ratings_count')->default(0);

            $table->decimal('average_rating', 3, 2)->default(0);

            $table->unsignedInteger('one_star')->default(0);
            $table->unsignedInteger('two_star')->default(0);
            $table->unsignedInteger('three_star')->default(0);
            $table->unsignedInteger('four_star')->default(0);
            $table->unsignedInteger('five_star')->default(0);

            $table->timestamp('last_review_at')->nullable();

            $table->timestamps();

            $table->unique([
                'reviewable_type',
                'reviewable_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_summaries');
    }
};
