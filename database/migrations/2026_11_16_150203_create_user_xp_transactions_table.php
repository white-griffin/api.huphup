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
        Schema::create('user_xp_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('user_xp_account_id')
                ->constrained('user_xp_accounts')
                ->restrictOnDelete();

            $table->unsignedBigInteger('amount');

            $table->unsignedTinyInteger('type');

            $table->string('source')->nullable();

            $table->unsignedBigInteger('source_id')->nullable();

            $table->string('idempotency_key')->unique();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['source', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_xp_transactions');
    }
};
