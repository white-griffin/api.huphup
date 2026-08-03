<?php

namespace App\Http\Controllers\Provider\Api\V1\Appointment;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\BusinessOffDayResource;
use App\Models\BusinessOffDay;
use Illuminate\Http\Response;

class BusinessOffDayController extends Controller
{
    public function index()
    {
        try {

            $offDays = BusinessOffDayResource::collection(
                app('business')
                    ->offDays()
                    ->orderBy('date')
                    ->get()
            );

            return ApiResponse::Success('عملیات موفق', $offDays);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    public function store()
    {
        $data = $this->offDaysData();
        try {
            app('business')->offDays()->create($data);

            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    public function destroy(BusinessOffDay $offDay)
    {
        try {
            $offDay->delete();
            return ApiResponse::Success('عملیات موفق');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    private function offDaysData()
    {
        return request()->validate([
            'date'        => 'required|date|unique:business_off_days,date,NULL,id,business_id,' . app('business')->id,
            'reason' => 'nullable|string|max:255',
        ]);
    }
}
