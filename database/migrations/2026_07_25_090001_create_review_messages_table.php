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
        Schema::create('review_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('review_messages')
                ->cascadeOnDelete();

            $table->nullableMorphs('author');

            $table->foreignId('business_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->text('body');

            $table->string('status')
                ->default(ReviewStatus::PENDING->value);

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamp('edited_at')
                ->nullable();

            $table->timestamps();

            $table->index(['review_id', 'status']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_messages');
    }
};
