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
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 50)->unique(); // dog, cat, bird, fish, ...
            $table->string('name_fa', 50); // سگ، گربه، پرنده، ماهی، ...
            $table->string('slug', 50)->unique(); // dog, cat, bird
            $table->string('icon')->nullable(); // emoji یا path آیکون
            $table->string('image')->nullable(); // تصویر نماینده
            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 0 For InActive');
            $table->unsignedInteger('order')->default(0); // ترتیب نمایش
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
