<?php

namespace App\Filament\Resources\PpidDocumentResource\Pages;

use App\Filament\Resources\PpidDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePpidDocument extends CreateRecord
{
    protected static string $resource = PpidDocumentResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk,
     * atau null bila tidak ada berkas (mencegah nilai basi tersimpan).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['file_size'] = filled($data['file_path'])
            ? PpidDocumentResource::resolveStoredFileSize($data['file_path'])
            : null;

        return $data;
    }
}
