<?php

namespace App\Filament\Resources\ProfileSectionResource\Pages;

use App\Filament\Resources\ProfileSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfileSections extends ListRecords
{
    protected static string $resource = ProfileSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
