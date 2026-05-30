<?php

use App\Enums\VerificationDocumentType;
use App\Enums\VerificationStatuses;
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
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->tinyInteger('document_type')
                ->default(VerificationDocumentType::NATIONAL_CARD_FRONT->value);

            // فایل
            $table->string('name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable(); // بایت

            // وضعیت بررسی هر مدرک
            $table->tinyInteger('verification_status')
                ->default(VerificationStatuses::PENDING->value)
                ->comment('0 For Pending,1 For UnderReview , 2 For Active , 3 For Suspended , 4 For Rejected');

            $table->timestamps();
            $table->index(['provider_id', 'document_type']);
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_documents');
    }
};
