<?php

namespace App\Http\Resources\V1\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'national_code' => $this->national_code,
            'father_name' => $this->father_name,
            'birth_date' => $this->birth_date,
            'gender_type' => $this->gender_type,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'two_factor_status' => $this->two_factor_status,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'shahkar_verified' => $this->shahkar_verified,
            'rejection_reason' => $this->rejection_reason,
            'documents' => $this->getDocs($this->documents),
            'businesses' => $this->getBusinesses($this->businesses)

        ];
    }

    private function getBusinesses($businesses): array
    {
        $businesses_data = [];

        foreach ($businesses as $business) {
            $businesses_data[] = [
                'id' => $business->id,
                'type' => $business->business_type,
                'name' => $business->name,
                'logo' => $business->logo_url,
                'activity_status' => $business->activity_status,
            ];
        }

        return $businesses_data;
    }

    private function getDocs($documents): array
    {
        $docs_data = [];

        foreach ($documents as $document) {
            $docs_data[] = [
                'id' => $document->id,
                'type' => $document->document_type,
                'image' => $document->image_url,
            ];
        }
        return $docs_data;
    }
}
