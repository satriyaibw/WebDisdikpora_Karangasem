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
     * Isi `file_size` otomatis dari berkas yang sudah tersimpan di disk,
     * atau null bila tidak ada berkas (mencegah nilai basi tersimpan).
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['file_size'] = filled($data['file_path'])
            ? SopDocumentResource::resolveStoredFileSize($data['file_path'])
            : null;

        return $data;
    }
}
