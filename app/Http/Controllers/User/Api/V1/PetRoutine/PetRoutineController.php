<?php

namespace App\Http\Controllers\User\Api\V1\PetRoutine;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\User\Api\V1\BaseController;
use App\Http\Requests\User\Api\V1\PetRoutine\StorePetRoutineRequest;
use App\Http\Requests\User\Api\V1\PetRoutine\UpdatePetRoutineRequest;
use App\Http\Resources\V1\User\PetRoutines\PetRoutineResource;
use App\Models\PetRoutine;
use App\Models\RoutineTemplate;
use App\Services\Routines\RoutineProgressService;
use App\Services\Routines\RoutineRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PetRoutineController extends BaseController
{
    /**
     * @description Pet Routines List
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $routines = PetRoutineResource::collection(
                PetRoutine::query()
                    ->where('pet_id', request()->pet_id)
                    ->with(['pet', 'template'])
                    ->latest()
                    ->get()
            );

            return ApiResponse::Success('لیست روتین‌ها دریافت شد', $routines);
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }


    /**
     * @description Create New Routine For Pet
     * @throws \Throwable
     */
    public function store(StorePetRoutineRequest $request)
    {
        $data = $request->data();

        DB::beginTransaction();

        try {

            // --------------------------------------------------
            // اگر interval یا next_due ارسال نشده باشد،
            // از مقدار پیش‌فرض template استفاده می‌کنیم
            // --------------------------------------------------
            $template = RoutineTemplate::query()->findOrFail($data['routine_template_id']);

            $data['interval_days'] = $data['interval_days'] ?? $template->default_interval_days;
            $data['next_due_at'] = $data['next_due_at'] ?? now()->addDays($data['interval_days']);
            $data['notification_enabled'] = $data['notification_enabled'] ?? true;

            $routine = PetRoutine::query()->create($data);

            DB::commit();

            return ApiResponse::Success(
                'روتین با موفقیت ثبت شد',
                PetRoutineResource::make($routine),
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در ثبت روتین'
            );
        }
    }


    /**
     * @description Show Single Routine
     * @param PetRoutine $pet_routine
     * @return JsonResponse
     */
    public function show(PetRoutine $pet_routine)
    {
        try {
            $pet_routine->load(['pet', 'template', 'template.actions']);

            $progress = app(RoutineProgressService::class)->calculate($pet_routine);
            $recommendations = app(RoutineRecommendationService::class)->getRecommendations($pet_routine);

            return ApiResponse::Success('اطلاعات روتین دریافت شد', [
                'routine' => $pet_routine,
                'progress' => $progress,
                'recommendations' => $recommendations,
            ]);
        }catch (\Exception $e) {
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }


    /**
     * @description Update Pet Routine
     * @param UpdatePetRoutineRequest $request
     * @param PetRoutine $pet_routine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(UpdatePetRoutineRequest $request, PetRoutine $pet_routine)
    {
        $data = $request->data();

        DB::beginTransaction();
        try {
            $pet_routine->update($data);
            DB::commit();
            return ApiResponse::Success('روتین با موفقیت بروزرسانی شد', $pet_routine->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در بروزرسانی روتین'
            );
        }
    }


    /**
     * @description  Destroy Routine
     * @param PetRoutine $pet_routine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function destroy(PetRoutine $pet_routine)
    {
        DB::beginTransaction();
        try {
            $pet_routine->delete();
            DB::commit();
            return ApiResponse::Success('روتین با موفقیت حذف شد');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::Fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'خطا در حذف روتین'
            );
        }
    }
}
