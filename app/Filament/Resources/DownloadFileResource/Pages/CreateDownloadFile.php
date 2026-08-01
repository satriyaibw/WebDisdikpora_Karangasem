<?php

namespace App\Filament\Resources\DownloadFileResource\Pages;

use App\Filament\Resources\DownloadFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadFile extends CreateRecord
{
    protected static string $resource = DownloadFileResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['file_path'])) {
            $size = DownloadFileResource::resolveStoredFileSize($data['file_path']);

            if ($size !== null) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
