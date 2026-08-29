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
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
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
            'verified_at' => $this->verified_at,
            'rejection_reason' => $this->rejection_reason,
            'activity_status' => $this->activity_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'commissions_count' => $this->commissions_count,
            'order_vendors_count' => $this->order_vendors_count,
            'products_count' => $this->products_count,
            'review_messages_count' => $this->review_messages_count,
            'reviews_count' => $this->reviews_count,
            'services_count' => $this->services_count,

            'provider_id' => $this->provider_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,

            'orderVendors' => OrderVendorResource::collection($this->whenLoaded('orderVendors')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'reviewMessages' => ReviewMessageResource::collection($this->whenLoaded('reviewMessages')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'services' => BusinessServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
