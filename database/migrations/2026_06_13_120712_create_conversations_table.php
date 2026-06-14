<?php

use App\Enums\AccessStatuses;
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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')
                ->default(AccessStatuses::PRIVATE->value)
                ->comment('1=Public,2=Private');

            $table->string('name')->nullable();          // فقط برای group
            $table->string('image')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // admin_id
            $table->boolean('activity_status')
                ->default(ActivityStatus::ACTIVE->value)
                ->comment('1=Active , 2=InActive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
