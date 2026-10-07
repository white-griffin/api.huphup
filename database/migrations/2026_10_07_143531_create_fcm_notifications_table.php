<?php

use App\Enums\FcmNotificationAudience;
use App\Enums\FcmNotificationStatus;
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
        Schema::create('fcm_notifications', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('body');

            $table->tinyInteger('audience_type')
                ->default(FcmNotificationAudience::ALL->value);

            $table->json('user_ids')->nullable();

            $table->tinyInteger('platform')
                ->nullable();

            $table->json('data')->nullable();

            $table->tinyInteger('status')
                ->default(FcmNotificationStatus::PENDING->value);

            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('processed_recipients')->default(0);
            $table->unsignedInteger('failed_recipients')->default(0);

            $table->timestamp('sent_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_notifications');
    }
};
