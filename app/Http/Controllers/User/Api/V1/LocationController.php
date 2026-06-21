<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Province;

class LocationController extends Controller
{
    public function provinces()
    {
        try {
            $provinces = Province::all();
            return ApiResponse::Success('عملیات موفق',$provinces);
        }catch (\Exception $exception){
            return ApiResponse::Fail(500, 'خطا در دریافت اطلاعات');
        }
    }
    public function cities()
    {
        try {
            $cities = City::all();
            return ApiResponse::Success('عملیات موفق',$cities);
        }catch (\Exception $exception){
            return ApiResponse::Fail(500, 'خطا در دریافت اطلاعات');
        }
    }


}
