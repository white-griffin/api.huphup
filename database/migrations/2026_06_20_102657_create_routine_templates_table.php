<?php

use App\Enums\ActivityStatus;
use App\Enums\RoutineCategoryTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('routine_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('species_id')->nullable()->constrained()->nullOnDelete();

            $table->tinyInteger('routine_category')
                ->default(RoutineCategoryTypes::HEALTH->value);
            $table->integer('default_interval_days')->default(5);

            $table->integer('reminder_days_before')->default(1);

            $table->string('image')->nullable();
            $table->text('description')->nullable();

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 2 For InActive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_templates');
    }
};
