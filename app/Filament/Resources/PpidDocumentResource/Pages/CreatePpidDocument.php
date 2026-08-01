<?php

namespace App\Filament\Resources\PpidDocumentResource\Pages;

use App\Filament\Resources\PpidDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePpidDocument extends CreateRecord
{
    protected static string $resource = PpidDocumentResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['file_path'])) {
            $size = PpidDocumentResource::resolveStoredFileSize($data['file_path']);

            if ($size !== null) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
