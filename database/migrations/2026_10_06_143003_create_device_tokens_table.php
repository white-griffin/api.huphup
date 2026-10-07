<?php

use App\Enums\PlatformTypes;
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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('token');
            $table->tinyInteger('platform')
                ->default(PlatformTypes::UNKNOWN); // android, ios
            $table->timestamp('last_seen_at')->nullable();
            $table->string('device_id');

            $table->timestamps();

            $table->unique(
                ['user_id', 'device_id'],
                'device_tokens_user_device_unique'
            );
            $table->index('user_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
