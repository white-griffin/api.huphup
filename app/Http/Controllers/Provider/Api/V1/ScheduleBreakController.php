<?php

namespace App\Http\Controllers\Provider\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\BusinessSchedule;
use App\Models\ScheduleBreak;
use Illuminate\Http\Response;

class ScheduleBreakController extends Controller
{
    public function store(int $scheduleId)
    {

        try {
            $schedule = BusinessSchedule::query()
                ->findOrFail($scheduleId);

            $data = request()->validate([
                'start_time' => 'required|date_format:H:i',
                'end_time'   => 'required|date_format:H:i|after:start_time',
            ]);

            $schedule->breaks()->create($data);

            return ApiResponse::success('عملیات موفق');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }

    }

    public function destroy(int $scheduleId, int $breakId)
    {
        try {
            $break = ScheduleBreak::query()
                ->where('schedule_id', $scheduleId)
                ->findOrFail($breakId);

            $break->delete();
            return ApiResponse::success('عملیات موفق');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
}
