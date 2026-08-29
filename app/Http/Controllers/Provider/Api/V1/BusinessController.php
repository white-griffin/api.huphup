<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\Api\V1\Business\UpdateBusinessRequest;
use App\Http\Resources\V1\Provider\BusinessResource;
use App\Models\Business;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function getBusinesses()
    {
        try {
            $businesses = BusinessResource::collection(
                request()->user('provider')
                    ->businesses()
                    ->with('reputation')
                    ->get()
            );
            return ApiResponse::Success('', $businesses);
        }catch (\Exception $exception){
            return ApiResponse::Fail(500,$exception->getMessage());
        }
    }

    public function showBusiness(Business $business)
    {
        try {
            return ApiResponse::Success('', BusinessResource::make($business));
        }catch (\Exception $exception){
            return ApiResponse::Fail(500,$exception->getMessage());
        }
    }

    public function update(UpdateBusinessRequest $request,Business $business)
    {
        try {
            $data = $request->validated();
            $media = app(MediaService::class);
            return DB::transaction(function () use ($business,$data,$media) {
                if (request()->hasFile('logo')) {
                    $data['logo'] = $media->replace(
                        $business->logo,
                        request()->file('logo'),
                        'businesses/logos'
                    );
                }
                if (request()->hasFile('cover_image')) {
                    $data['cover_image'] = $media->replace(
                        $business->cover_image,
                        request()->file('cover_image'),
                        'businesses/covers'
                    );
                }

                $business->update($data);
                return ApiResponse::Success('عملیات موفق');
            });
        }catch (\Exception $exception){
            return ApiResponse::Fail(500,$exception->getMessage());
        }
    }
}
