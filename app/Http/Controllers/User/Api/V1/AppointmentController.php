<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\AppointmentResource;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Http\Response;
use PHPUnit\Exception;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $service) {}

    public function index()
    {
        try {
            $appointments = AppointmentResource::collection(
                Appointment::query()
                    ->where('user_id', auth()->id())
                    ->with(['service', 'pet', 'business'])
                    ->orderByDesc('starts_at')
                    ->get()
            );

            return ApiResponse::success('عملیات موفق',$appointments);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
    public function availableSlots(int $businessId)
    {
        request()->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date|after_or_equal:today',
        ]);

        try {
            $service = Service::query()
                ->findOrFail(request()->service_id);

            $slots = $this->service->getAvailableSlots($businessId, $service, request()->date);

            return ApiResponse::success('عملیات موفق',$slots);

        }catch (\Exception $exception){
            report($exception);

            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    public function store()
    {
        $data = $this->validateAppointmentData();
        try {
            $service = Service::query()
                ->findOrFail($data['service_id']);

            $appointment = $this->service->book(
                businessId: $data['business_id'],
                service:    $service,
                petId:      $data['pet_id'],
                userId:     auth()->id(),
                startsAt:   $data['starts_at'],
                note: $data['note']
            );

            return ApiResponse::Success('عملیات موفق',$appointment);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }


    public function cancel(Appointment $appointment)
    {
        try {

            if ($appointment->starts_at->isPast()){
                return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY,'تاریخ رزرو گذشته است');
            }

            $appointment->update(['status' => 'cancelled']);
            return ApiResponse::Success('عملیات موفق');

        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }

    }

    private function validateAppointmentData(): array
    {
        return request()->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id'  => 'required|exists:services,id',
            'pet_id'      => 'required|exists:pets,id',
            'starts_at'   => 'required|date|after_or_equal:now',
        ]);
    }
}
