<?php

use App\Enums\ActivityStatus;
use App\Enums\RoutineStatuses;
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
        Schema::create('pet_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_template_id')->constrained()->cascadeOnDelete();

            $table->integer('interval_days');

            $table->date('start_date');

            $table->timestamp('last_done_at')->nullable();
            $table->timestamp('next_due_at')->nullable();

            $table->boolean('notification_enabled')->default(true);

            $table->tinyInteger('routine_status')
                ->default(RoutineStatuses::UPCOMING->value)
                ->comment('1=Upcoming, 2=Due Soon, 3=Due Today, 4=Over Due, 5=Paused, 6=Archived');

            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('next_due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_routines');
    }
};
