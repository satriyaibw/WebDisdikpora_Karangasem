<?php

namespace App\Filament\Resources\SopDocumentResource\Pages;

use App\Filament\Resources\SopDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSopDocument extends CreateRecord
{
    protected static string $resource = SopDocumentResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['file_path'])) {
            $size = SopDocumentResource::resolveStoredFileSize($data['file_path']);

            if ($size !== null) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
