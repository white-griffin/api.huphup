<?php

namespace App\Http\Controllers\User\Api\V1\User;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\User\UserNoticeResource;
use App\Models\UserNotice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserNoticeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $notices = UserNotice::query()
                ->where(function ($query) use ($request) {
                    $query
                        ->whereNull('user_id')
                        ->orWhere('user_id', $request->user()->id);
                })
                ->latest()
                ->paginate();

            return ApiResponse::Success('عملیات موفق',UserNoticeResource::collection($notices));
        }catch (\Exception $exception){
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage());
        }
    }
}
