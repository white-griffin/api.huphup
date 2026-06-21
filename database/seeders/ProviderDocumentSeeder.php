<?php

namespace Database\Seeders;

use App\Enums\FileTypes;
use App\Enums\VerificationDocumentType;
use App\Enums\VerificationStatuses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderDocumentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('provider_documents')->insert([
            [
                'provider_id' => 1,
                'document_type' => VerificationDocumentType::NATIONAL_CARD_FRONT->value,
                'name' => 'provider-documents/provider-1-national-card-front.jpg',
                'mime_type' => FileTypes::IMAGE_JPEG->value,
                'size' => 120000,
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'provider_id' => 2,
                'document_type' => VerificationDocumentType::NATIONAL_CARD_FRONT->value,
                'name' => 'provider-documents/provider-2-national-card-front.jpg',
                'mime_type' => FileTypes::IMAGE_JPEG->value,
                'size' => 125000,
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
