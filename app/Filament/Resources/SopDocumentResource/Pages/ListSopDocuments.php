<?php

namespace App\Filament\Resources\SopDocumentResource\Pages;

use App\Filament\Resources\SopDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSopDocuments extends ListRecords
{
    protected static string $resource = SopDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
