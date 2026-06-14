<?php

namespace App\Http\Controllers\User\Api\V1\Chat;

use App\Enums\AccessStatuses;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Chat\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // لیست مکالمات کاربر
    public function index()
    {
        try {
            $conversations = ConversationResource::collection(
                Auth::user()
                    ->conversations()
                    ->with(['latestMessage', 'participants'])
                    ->withCount(['messages as unread_count' => function ($q) {
                        $q->whereNull('read_at')
                            ->where('sender_id', '!=', Auth::id());
                    }])
                    ->latest()
                    ->cursorPaginate(20)
            );

            return ApiResponse::success('عملیات موفق', $conversations);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    // شروع private chat با یوزر دیگه
    public function store()
    {
        request()->validate([
            'user_id' => 'required|exists:users,id|different:' . Auth::id(),
        ]);

        try {
            $targetUser = User::query()
            ->findOrFail(request()->user_id);

            // اگر قبلاً conversation private بین این دو نفر وجود داره، همونو برگردون
            $existing = Auth::user()
                ->conversations()
                ->where('type', AccessStatuses::PRIVATE->value)
                ->whereHas('participants', fn($q) => $q->where('user_id', $targetUser->id))
                ->first();

            if ($existing) {
                return ApiResponse::Success('عملیات موفق',ConversationResource::make(
                    $existing->load(['participants', 'latestMessage'])
                ));
            }

            $conversation = Conversation::query()
                ->create(['type' => AccessStatuses::PRIVATE->value]);
            $conversation->participants()->attach([
                Auth::id()        => ['joined_at' => now()],
                $targetUser->id   => ['joined_at' => now()],
            ]);

            return ApiResponse::Success('عملیات موفق',ConversationResource::make(
                $conversation->load(['participants', 'latestMessage'])
            ));

        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    // جزئیات یک conversation
    public function show(Conversation $conversation)
    {
        try {
            $this->authorizeParticipant($conversation);

            return ApiResponse::Success('عملیات موفق',
                ConversationResource::make($conversation->load(['participants', 'latestMessage']))
            );
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در دریافت اطلاعات');
        }
    }

    // لیست گروه‌های عمومی (با وضعیت عضویت)
    public function groups()
    {
        try {
            $groups = Conversation::query()
                ->where('type', AccessStatuses::PUBLIC->value)
                ->withCount('participants')
                ->with('creator:id,first_name,last_name')
                ->paginate(15);

            return ApiResponse::success('عملیات موفق',
                ConversationResource::collection($groups)
            );
        } catch (\Exception $e) {
            report($e);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در دریافت اطلاعات');
        }
    }

// عضویت در گروه
    public function join(Conversation $conversation)
    {
        try {
            if ($conversation->type != AccessStatuses::PUBLIC->value) {
                return ApiResponse::Fail(Response::HTTP_BAD_REQUEST, 'این مکالمه گروهی نیست');
            }

            if ($conversation->participants()->where('user_id', Auth::id())->exists()) {
                return ApiResponse::Fail(Response::HTTP_UNPROCESSABLE_ENTITY, 'قبلاً عضو شده‌اید');
            }

            $conversation->participants()->attach(Auth::id(), ['joined_at' => now()]);

            return ApiResponse::success('با موفقیت عضو شدید');
        } catch (\Exception $e) {
            report($e);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    // خروج از گروه (برای group conversations)
    public function leave(Conversation $conversation)
    {
        try {
            $this->authorizeParticipant($conversation);

            $conversation->participants()->detach(Auth::id());

            return ApiResponse::Success('از مکالمه خارج شدید');
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
