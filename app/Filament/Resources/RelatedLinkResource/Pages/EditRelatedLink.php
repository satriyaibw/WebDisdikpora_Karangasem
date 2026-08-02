<?php

namespace App\Filament\Resources\RelatedLinkResource\Pages;

use App\Filament\Resources\RelatedLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRelatedLink extends EditRecord
{
    protected static string $resource = RelatedLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
