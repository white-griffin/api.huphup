<?php

use App\Enums\MemberActivityStatuses;
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
        Schema::create('user_type_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_type_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('activity_status')
                ->default(MemberActivityStatuses::PENDING->value)
                ->comment('0 For Pending,1 For Active , 2 For Suspended , 3 For Rejected');
            $table->timestamp('verified_at')->nullable(); // زمان تایید
            $table->text('rejection_reason')->nullable(); // دلیل رد درخواست
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_type_users');
    }
};
