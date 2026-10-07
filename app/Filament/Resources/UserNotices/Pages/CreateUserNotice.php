<?php

namespace App\Filament\Resources\UserNotices\Pages;

use App\Enums\UserNoticeTypes;
use App\Filament\Resources\UserNotices\UserNoticeResource;
use App\Models\User;
use App\Services\UserDomain\UserNoticeService;
use Filament\Resources\Pages\CreateRecord;
use League\HTMLToMarkdown\HtmlConverter;

class CreateUserNotice extends CreateRecord
{
    protected static string $resource = UserNoticeResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $service = app(UserNoticeService::class);

        $converter = new HtmlConverter();


        $type = UserNoticeTypes::from($data['type']);

        if ($data['target'] === 'all') {
            $notice = $service->sendToAll(
                title: $data['title'],
                body: $converter->convert($data['body']),
                type: $type,
                data: $data['data'] ?? [],
            );

            $this->record = $notice;

            return $notice;
        }

        if ($data['target'] === 'one') {
            $notice = $service->sendToUser(
                user: User::find($data['user_id']),
                title: $data['title'],
                body: $converter->convert($data['body']),
                type: $type,
                data: $data['data'] ?? [],
            );

            $this->record = $notice;

            return $notice;
        }

        $users = User::query()
            ->whereIn('id', $data['user_ids'])
            ->get();

        $service->sendToUsers(
            users: $users,
            title: $data['title'],
            body: $converter->convert($data['body']),
            type: $type,
            data: $data['data'] ?? [],
        );

        $notice = $service->sendToUser(
            user: $users->first(),
            title: $data['title'],
            body: $converter->convert($data['body']),
            type: $type,
            data: $data['data'] ?? [],
        );

        // حذف Notice اضافه‌ای که فقط برای ساختن record ایجاد شد
        $notice->delete();

        return $notice;
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
