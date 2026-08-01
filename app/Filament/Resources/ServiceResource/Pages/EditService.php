<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['form_template'])) {
            $size = ServiceResource::resolveStoredFileSize($data['form_template']);

            if ($size !== null) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
