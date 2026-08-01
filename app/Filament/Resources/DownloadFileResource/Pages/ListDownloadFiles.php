<?php

namespace App\Filament\Resources\DownloadFileResource\Pages;

use App\Filament\Resources\DownloadFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDownloadFiles extends ListRecords
{
    protected static string $resource = DownloadFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
