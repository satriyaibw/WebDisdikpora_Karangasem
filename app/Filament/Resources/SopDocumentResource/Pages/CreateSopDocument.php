<?php

namespace App\Filament\Resources\SopDocumentResource\Pages;

use App\Filament\Resources\SopDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSopDocument extends CreateRecord
{
    protected static string $resource = SopDocumentResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk,
     * atau null bila tidak ada berkas (mencegah nilai basi tersimpan).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['file_size'] = filled($data['file_path'])
            ? SopDocumentResource::resolveStoredFileSize($data['file_path'])
            : null;

        return $data;
    }
}
