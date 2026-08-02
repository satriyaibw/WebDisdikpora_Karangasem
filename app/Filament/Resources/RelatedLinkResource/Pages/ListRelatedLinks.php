<?php

namespace App\Filament\Resources\RelatedLinkResource\Pages;

use App\Filament\Resources\RelatedLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRelatedLinks extends ListRecords
{
    protected static string $resource = RelatedLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
