<?php

namespace App\Filament\Resources\FcmNotifications\Pages;

use App\Filament\Resources\FcmNotifications\FcmNotificationResource;
use App\Jobs\SendFcmNotificationJob;
use Auth;
use Filament\Resources\Pages\CreateRecord;
use League\HTMLToMarkdown\HtmlConverter;

class CreateFcmNotification extends CreateRecord
{
    protected static string $resource = FcmNotificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        $converter = new HtmlConverter();

        $data['body'] = $converter->convert($data['body']);

        return $data;
    }

    protected function afterCreate(): void
    {
        SendFcmNotificationJob::dispatch(
            $this->record->id
        );
    }
}
