<?php

namespace App\Filament\Resources\SopDocumentResource\Pages;

use App\Filament\Resources\SopDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSopDocument extends EditRecord
{
    protected static string $resource = SopDocumentResource::class;

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
        if (! empty($data['file_path'])) {
            $size = SopDocumentResource::resolveStoredFileSize($data['file_path']);

            if ($size !== null) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
