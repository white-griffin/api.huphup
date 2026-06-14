<?php

namespace App\Http\Controllers\User\Api\V1\Chat;

use App\Enums\MessageTypes;
use App\Events\User\MessageSent;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Chat\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // تاریخچه پیام‌ها
    public function index(Conversation $conversation)
    {
        try {
            $this->authorizeParticipant($conversation);

            $messages = MessageResource::collection(
                $conversation->messages()
                    ->with('sender:id,first_name,last_name,avatar')
                    ->latest()
                    ->paginate(50)
            );

            return ApiResponse::Success('عملیات موفق', $messages);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    // ارسال پیام
    public function store(Request $request, Conversation $conversation)
    {
        try {
            $this->authorizeParticipant($conversation);

            $request->validate([
                'body' => 'required|string|max:5000',
//                'type' => 'in:text,image,file',
            ]);

            $message = $conversation->messages()->create([
                'sender_id' => Auth::id(),
                'body'      => $request->body,
                'type'      => $request->type ?? MessageTypes::TEXT->value,
            ]);

            $message->load('sender:id,first_name,last_name,avatar');

            // Broadcast real-time
            broadcast(new MessageSent($message))->toOthers();

            return ApiResponse::Success('عملیات موفق',MessageResource::make($message));
        }catch (\Exception $exception){
            report($exception);

            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    // علامت‌گذاری خوانده‌شده
    public function markAsRead(Conversation $conversation)
    {
        try {
            $this->authorizeParticipant($conversation);

            // آپدیت last_read_at در pivot
            $conversation->participants()
                ->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

            // علامت‌گذاری پیام‌ها
            $conversation->messages()
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return ApiResponse::Success('پیام خوانده شد');
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }
    private function authorizeParticipant(Conversation $conversation): void
    {
        abort_unless(
            $conversation->participants()->where('user_id', Auth::id())->exists(),
            Response::HTTP_FORBIDDEN,
            'عدم دسترسی به مکالمه'
        );
    }
}
