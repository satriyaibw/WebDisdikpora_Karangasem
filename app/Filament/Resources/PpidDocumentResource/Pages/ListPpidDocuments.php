<?php

namespace App\Filament\Resources\PpidDocumentResource\Pages;

use App\Filament\Resources\PpidDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPpidDocuments extends ListRecords
{
    protected static string $resource = PpidDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
