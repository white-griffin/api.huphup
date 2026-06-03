<?php

use App\Enums\ActivityStatus;
use App\Enums\CategoryTypes;
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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();

            $table->tinyInteger('type')
                ->default(CategoryTypes::PRODUCT->value)
                ->comment('1 For Product , 2 For Service , 3 For Blog');

            $table->tinyInteger('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1 For Active , 2 For InActive');

            $table->timestamps();

            $table->unique(['business_id', 'slug']); // اسلاگ در هر کسب‌وکار یکتاست
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
