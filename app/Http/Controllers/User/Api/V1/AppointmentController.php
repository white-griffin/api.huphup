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
    public function availableSlots(int $businessId)
    {
        request()->validate([
            'business_service_id' => 'required|exists:business_services,id',
            'date'       => 'required|date|after_or_equal:today',
        ]);

        try {

            $businessService = BusinessService::query()
                ->where('id', request()->business_service_id)
                ->firstOrFail();

            $slots = $this->service->getAvailableSlots($businessId, request()->date, $businessService->duration);

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
                'appointment' => $appointment,
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
            abort_if($appointment->user_id !== auth()->id(), Response::HTTP_FORBIDDEN);

            $appointmentStartsAt = Carbon::parse(
                $appointment->date->toDateString() . ' ' . $appointment->start_time->format('H:i:s')
            );

            if ($appointmentStartsAt->isPast()){
                return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY,'تاریخ رزرو گذشته است');
            }

            $appointment->update(['status' => AppointmentStatuses::CANCELLED->value]);
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
            'business_service_id'  => 'required|exists:business_service,id',
            'pet_id'      => [
                'required',
                Rule::exists('pets', 'id')->where('user_id', auth()->id()),
            ],
            'starts_at'   => 'required|date|after_or_equal:now',
            'note'        => 'nullable|string',
            'coupon_code' => ['nullable', 'string', 'max:50'],
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
