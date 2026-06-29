<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\PublicationStatus;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('managePublication')
                ->label('مدیریت انتشار')
                ->icon('heroicon-o-check-circle')
                ->color('warning')

                ->schema([

                    Select::make('publication_status')
                        ->label('وضعیت انتشار')
                        ->options(PublicationStatus::labels())
                        ->required()
                        ->live(),

                    Textarea::make('reject_reason')
                        ->label('دلیل رد')
                        ->rows(3)
                        ->visible(fn ($get) =>
                            $get('publication_status') == PublicationStatus::REJECTED->value
                        )
                        ->required(fn ($get) =>
                            $get('publication_status') == PublicationStatus::REJECTED->value
                        ),
                ])

                ->action(function (array $data) {

                    $this->record->update([
                        'publication_status' => $data['publication_status'],
                        'reject_reason' => $data['publication_status'] == PublicationStatus::REJECTED->value
                            ? $data['reject_reason']
                            : null,
                    ]);
                })

                ->modalHeading('مدیریت وضعیت انتشار')
                ->modalSubmitActionLabel('ثبت تغییرات'),
        ];
    }
}
