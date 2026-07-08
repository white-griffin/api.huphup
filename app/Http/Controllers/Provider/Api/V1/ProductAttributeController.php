<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Enums\ActivityStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Products\AttributeResource;
use App\Models\Attribute;

class ProductAttributeController extends Controller
{
    public function index()
    {
        try {
            $attributes = AttributeResource::collection(
                Attribute::query()
                    ->with('options')
                    ->where('activity_status', ActivityStatus::ACTIVE->value)
                    ->get()
            );
            return ApiResponse::success('عملیات موفق', $attributes);
        } catch (\Exception $exception) {
            return ApiResponse::Fail('خطا در دریافت اطلاعات', $exception->getMessage());
        }
    }
}
