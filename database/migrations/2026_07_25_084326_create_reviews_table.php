<?php

use App\Enums\ReviewStatus;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->nullableMorphs('reviewable');

            $table->unsignedTinyInteger('rating')
                ->nullable();

            $table->string('title')
                ->nullable();

            $table->text('body')
                ->nullable();

            $table->string('status')
                ->default(ReviewStatus::PENDING->value);

            $table->boolean('is_verified_purchase')
                ->default(false);

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamp('edited_at')
                ->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);

            $table->index(['reviewable_type', 'reviewable_id', 'status']);

            $table->unique(
                ['user_id', 'reviewable_type', 'reviewable_id'],
                'reviews_unique_user_review'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
