<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\BusinessScheduleResource;
use App\Models\BusinessSchedule;
use Illuminate\Http\Response;

class ScheduleController extends Controller
{
    public function index()
    {
        try {
            $business = app('business');

            $businessSchedules = BusinessScheduleResource::collection(
                BusinessSchedule::with('breaks')
                    ->where('business_id', $business->id)
                    ->get()
            );

            return ApiResponse::Success('عملیات موفق', $businessSchedules);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    public function upsert()
    {
        $data = $this->validateScheduleData();

        try {
            $business = app('business');

            $schedules = collect($data)->map(function ($row) use ($business) {
                return BusinessSchedule::query()
                    ->updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'day_of_week' => $row['day_of_week']],
                        [
                            'start_time' => $row['start_time'],
                            'end_time' => $row['end_time'],
                            'slot_duration' => $row['slot_duration'],
                            'capacity' => $row['capacity'],
                            'activity_status' => $row['activity_status'],
                        ]
                    );
            });

            return ApiResponse::Success('عملیات موفق',
                BusinessScheduleResource::collection($schedules)
            );
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    private function validateScheduleData()
    {
        return request()->validate([
            '*.day_of_week' => 'required|integer|between:1,7',
            '*.start_time' => 'required|date_format:H:i',
            '*.end_time' => 'required|date_format:H:i|after:*.start_time',
            '*.slot_duration' => 'required|integer|min:5',
            '*.capacity' => 'required|integer|min:1',
            '*.activity_status' => 'required|boolean',
        ]);

    }
}
