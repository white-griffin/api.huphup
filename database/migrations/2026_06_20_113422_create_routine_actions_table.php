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
        Schema::create('routine_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_template_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('target_type');
            $table->unsignedBigInteger('target_id');

            $table->unsignedInteger('priority')->default(1);
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index(['routine_template_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_actions');
    }
};
