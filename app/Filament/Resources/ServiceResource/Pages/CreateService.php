<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
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
