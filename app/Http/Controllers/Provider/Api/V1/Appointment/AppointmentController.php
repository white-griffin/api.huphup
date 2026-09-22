<?php

namespace App\Http\Controllers\Provider\Api\V1\Appointment;

use App\Enums\AppointmentStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Provider\Appointment\AppointmentResource;
use App\Models\Appointment;
use App\Services\Appointment\AppointmentService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{

    public function index(Request $request)
    {

        $data = $request->validate([
            'status'              => ['nullable', Rule::enum(AppointmentStatuses::class)],
            'date'                => ['nullable', 'date'],
            'from_date'           => ['nullable', 'date'],
            'to_date'             => ['nullable', 'date', 'after_or_equal:from_date'],
            'business_service_id' => ['nullable', 'integer', 'exists:business_services,id'],
            'per_page'            => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $appointments = Appointment::query()
            ->with([
                'user',
                'pet',
                'businessService',
                'payments',
            ])
            ->when(
                $data['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $data['date'] ?? null,
                fn ($query, $date) => $query->whereDate('date', $date)
            )
            ->when(
                $data['from_date'] ?? null,
                fn ($query, $date) => $query->whereDate('date', '>=', $date)
            )
            ->when(
                $data['to_date'] ?? null,
                fn ($query, $date) => $query->whereDate('date', '<=', $date)
            )
            ->when(
                $data['business_service_id'] ?? null,
                fn ($query, $serviceId) => $query->where(
                    'business_service_id',
                    $serviceId
                )
            )
            ->latest('date')
            ->latest('start_time')
            ->paginate($data['per_page'] ?? 20);

        return ApiResponse::Success('لیست رزروها', [
            'appointments' => AppointmentResource::collection($appointments),
        ]);
    }

    public function confirm(Appointment $appointment)
    {
        try {

            $appointment = app(AppointmentService::class)
                ->confirm($appointment);

            return ApiResponse::Success('رزرو تایید شد', [
                'appointment' => AppointmentResource::make($appointment),
            ]);

        } catch (\DomainException $exception) {
            return ApiResponse::Fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception->getMessage()
            );

        } catch (\Exception $exception) {
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در عملیات'
            );
        }
    }

    public function reject(Appointment $appointment)
    {
        try {


            $appointment = app(AppointmentService::class)
                ->reject($appointment);

            return ApiResponse::Success('رزرو رد شد', [
                'appointment' => AppointmentResource::make($appointment),
            ]);

        } catch (\DomainException $exception) {
            return ApiResponse::Fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception->getMessage()
            );

        } catch (\Exception $exception) {
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در عملیات'
            );
        }
    }

    public function complete(Appointment $appointment)
    {
        try {
            $appointment = app(AppointmentService::class)
                ->complete($appointment);

            return ApiResponse::Success('ویزیت با موفقیت تکمیل شد');

        } catch (DomainException $exception) {
            return ApiResponse::Fail(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception->getMessage()
            );

        } catch (\Exception $exception) {
            report($exception);

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در عملیات'
            );
        }
    }
}
