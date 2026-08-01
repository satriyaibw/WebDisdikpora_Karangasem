<?php

namespace App\Filament\Resources\DownloadFileResource\Pages;

use App\Filament\Resources\DownloadFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadFile extends CreateRecord
{
    protected static string $resource = DownloadFileResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk,
     * atau null bila tidak ada berkas (mencegah nilai basi tersimpan).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['file_size'] = filled($data['file_path'])
            ? DownloadFileResource::resolveStoredFileSize($data['file_path'])
            : null;

        return $data;
    }
}
