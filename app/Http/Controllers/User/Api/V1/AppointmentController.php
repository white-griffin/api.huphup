<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\AppointmentStatuses;
use App\Enums\PaymentGateways;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\User\AppointmentResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Appointment;
use App\Models\BusinessService;
use App\Services\Appointment\AppointmentService;
use App\Services\Payment\PaymentService;
use App\Services\Review\ReviewService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $service) {}

    public function index()
    {
        try {
            $appointments = AppointmentResource::collection(
                Appointment::query()
                    ->where('user_id', auth()->id())
                    ->with(['businessService', 'pet', 'business'])
                    ->orderByDesc('date')
                    ->orderByDesc('start_time')
                    ->get()
            );

            return ApiResponse::success('عملیات موفق',$appointments);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
//    public function availableSlots(int $businessId)
//    {
//        request()->validate([
//            'business_service_id' => 'required|exists:business_services,id',
//            'date'       => 'required|date|after_or_equal:today',
//        ]);
//
//        try {
//
//            $businessService = BusinessService::query()
//                ->where('id', request()->business_service_id)
//                ->firstOrFail();
//
//            $slots = $this->service->getAvailableSlots($businessId, request()->date, $businessService->duration);
//
//            return ApiResponse::success('عملیات موفق',$slots);
//
//        }catch (\Exception $exception){
//            report($exception);
//            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
//        }
//    }

    public function availableSlots(
        int $businessId,
        int $serviceDuration,
        int $days = 7
    ): JsonResponse
    {
        request()->validate([
            'business_service_id' => 'required|exists:business_services,id',
        ]);

        $startDate = today();

        $slots =  collect(range(0, $days - 1))
            ->map(function (int $day) use (
                $businessId,
                $serviceDuration,
                $startDate
            ) {
                $date = $startDate->copy()->addDays($day);

                $businessService = BusinessService::query()
                    ->where('id', request()->business_service_id)
                    ->firstOrFail();

                return [
                    'date' => $date->toDateString(),
                    'slots' => $this->service->getAvailableSlots($businessId, request()->date, $businessService->duration)(
                        businessId: $businessId,
                        date: $date->toDateString(),
                        serviceDuration: $serviceDuration,
                    )->values()->all(),
                ];
            });

        return ApiResponse::success('عملیات موفق',$slots);
    }

    public function store()
    {
        $data = $this->validateAppointmentData();
        try {
            $businessService = BusinessService::query()
                ->findOrFail($data['business_service_id']);

            $appointment = $this->service->book(
                $businessService,
                petId:      $data['pet_id'],
                userId:     auth()->id(),
                startsAt:   $data['starts_at'],
                note: $data['note'] ?? null
            );

            $paymentResult = app(PaymentService::class)->pay(
                payable: $appointment,
                gateway: PaymentGateways::from($data['gateway']),
                couponCode: $data['coupon_code'] ?? null,
            );

            return ApiResponse::Success('رزرو ثبت شد', [
                'appointment' => AppointmentResource::make($appointment),
                'payment' => $paymentResult,
            ]);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    public function cancel(Appointment $appointment)
    {
        try {
            abort_if(
                $appointment->user_id != auth()->id(),
                Response::HTTP_FORBIDDEN
            );

            $appointmentStartsAt = Carbon::parse(
                $appointment->date->toDateString()
                . ' '
                . $appointment->start_time->format('H:i:s')
            );

            if ($appointmentStartsAt->isPast()) {
                return ApiResponse::Fail(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'تاریخ رزرو گذشته است'
                );
            }

            app(AppointmentService::class)->cancel($appointment);

            return ApiResponse::Success('عملیات موفق');

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
    private function validateAppointmentData(): array
    {
        return request()->validate([
            'business_id' => 'required|exists:businesses,id',
            'business_service_id'  => 'required|exists:business_services,id',
            'pet_id'      => [
                'required',
                Rule::exists('pets', 'id')->where('user_id', auth()->id()),
            ],
            'starts_at'   => 'required|date|after_or_equal:now',
            'note'        => 'nullable|string',
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'gateway' => ['required', Rule::enum(PaymentGateways::class)],
        ]);
    }

    public function review(
        StoreReviewRequest $request,
        Appointment $appointment,
        ReviewService $reviewService,
    ) {
        try {
            abort_unless(
                $appointment->user_id === $request->user()->id,
                Response::HTTP_FORBIDDEN
            );

            $review = $reviewService->create(
                source: $appointment,
                attributes: $request->validated(),
            );

            return ApiResponse::success(
                'نظر با موفقیت ثبت شد.',
                ReviewResource::make(
                    $review->load([
                        'user',
                        'messages.author',
                        'messages.business',
                    ])
                )
            );
        } catch (\Throwable $e) {
            return ApiResponse::fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }
}
