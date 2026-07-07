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
        Schema::create('product_variation_attributes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variation_id')->constrained()->cascadeOnDelete();

            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();

            $table->foreignId('attribute_option_id')->constrained()->cascadeOnDelete();

            $table->unique([
                'product_variation_id',
                'attribute_id'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_attributes');
    }
};
