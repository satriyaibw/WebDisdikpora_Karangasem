<?php

namespace App\Filament\Resources\ProfileSectionResource\Pages;

use App\Filament\Resources\ProfileSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfileSection extends EditRecord
{
    protected static string $resource = ProfileSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
