<?php

use App\Enums\ActivityStatus;
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
        Schema::create('business_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 1=Sat ... 7=Fri
            $table->time('open_time');
            $table->time('close_time');
            $table->unsignedInteger('slot_duration')->default(30); // دقیقه
            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 0 For InActive');
            $table->unique(['business_id', 'day_of_week']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_schedules');
    }
};
