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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('business_type');

            $table->decimal('min_rating', 3, 2);
            $table->decimal('max_rating', 3, 2);

            $table->decimal('commission_rate', 5, 2);

            $table->unsignedInteger('priority')->default(0);

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
