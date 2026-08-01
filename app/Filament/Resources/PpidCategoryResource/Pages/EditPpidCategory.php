<?php

namespace App\Filament\Resources\PpidCategoryResource\Pages;

use App\Filament\Resources\PpidCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPpidCategory extends EditRecord
{
    protected static string $resource = PpidCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
