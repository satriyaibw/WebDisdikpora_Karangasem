<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk,
     * atau null bila tidak ada berkas (mencegah nilai basi tersimpan).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['file_size'] = filled($data['form_template'])
            ? ServiceResource::resolveStoredFileSize($data['form_template'])
            : null;

        return $data;
    }
}
