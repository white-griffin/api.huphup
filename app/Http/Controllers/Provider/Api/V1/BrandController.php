<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Products\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    public function index()
    {
        try {
            $brands = BrandResource::collection(Brand::all());
            return ApiResponse::success('عملیات موفق',$brands);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
}
