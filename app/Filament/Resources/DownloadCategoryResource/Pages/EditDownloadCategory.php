<?php

namespace App\Filament\Resources\DownloadCategoryResource\Pages;

use App\Filament\Resources\DownloadCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDownloadCategory extends EditRecord
{
    protected static string $resource = DownloadCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
