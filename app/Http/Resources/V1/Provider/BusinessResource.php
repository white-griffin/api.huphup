<?php

namespace App\Http\Resources\V1\Provider;

use App\Http\Resources\V1\Provider\Products\ProductResource;
use App\Http\Resources\V1\User\BusinessServiceResource;
use App\Http\Resources\V1\User\Orders\OrderVendorResource;
use App\Http\Resources\V1\User\ReviewMessageResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Business */
class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_type' => $this->business_type,
            'name' => $this->name,
            'license_code' => $this->license_code,
            'description' => $this->description,
            'logo' => $this->logo_url,
            'cover_image' => $this->cover_url,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bank_name' => $this->bank_name,
            'bank_account_holder' => $this->bank_account_holder,
            'bank_card' => $this->bank_card,
            'bank_iban' => $this->bank_iban,
            'settings' => $this->settings,
            'verification_status' => $this->verification_status,
            'rejection_reason' => $this->rejection_reason,
            'activity_status' => $this->activity_status,
            'created_at' => $this->created_at,

            'province_id' => $this->province_id,
            'city_id' => $this->city_id,

            'rating_avg' => $this->reputation?->rating_avg,
            'rating_count' => $this->reputation?->rating_count,
            'review_count' => $this->reputation?->review_count,
            'reputation_score' => $this->reputation?->reputation_score,
            'current_commission_rate' => $this->reputation?->current_commission_rate,
            'last_calculated_at' => $this->reputation?->last_calculated_at,

        ];
    }
}
