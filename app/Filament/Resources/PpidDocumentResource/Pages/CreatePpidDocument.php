<?php

namespace App\Filament\Resources\PpidDocumentResource\Pages;

use App\Filament\Resources\PpidDocumentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreatePpidDocument extends CreateRecord
{
    protected static string $resource = PpidDocumentResource::class;

    /**
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->fillFileSize($data);
    }

    private function fillFileSize(array $data): array
    {
        if (! empty($data['file_path'])) {
            $size = Storage::disk('public')->size($data['file_path']);

            if ($size !== false) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
