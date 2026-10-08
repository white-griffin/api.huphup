<?php

use App\Enums\ActivityStatus;
use App\Enums\BannerPlacement;
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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            // Banner images by context/device/aspect ratio.
            $table->json('images');

            $table->tinyInteger('placement')
                ->default(BannerPlacement::HOME->value);

            // Optional polymorphic target.
            $table->nullableMorphs('target');

            // Optional URL target.
            $table->string('target_url')->nullable();

            // Display order within a placement.
            $table->unsignedInteger('sort_order')->default(0);

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value);

            // Optional scheduling.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index([
                'placement',
                'is_active',
                'sort_order',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
