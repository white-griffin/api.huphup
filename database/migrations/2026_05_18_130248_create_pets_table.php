<?php

use App\Enums\GenderType;
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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->constrained();
            $table->foreignId('breed_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->tinyInteger('gender_type')
                ->default(GenderType::MALE->value)
                ->comment('1 For Male , 2 For FeMale , 0 For Unknown');

            $table->date('birth_date')->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->string('color', 50)->nullable();
            $table->string('avatar')->nullable();
            $table->json('medical_records')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
