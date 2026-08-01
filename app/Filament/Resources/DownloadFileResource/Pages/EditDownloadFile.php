<?php

namespace App\Filament\Resources\DownloadFileResource\Pages;

use App\Filament\Resources\DownloadFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDownloadFile extends EditRecord
{
    protected static string $resource = DownloadFileResource::class;

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
            $size = DownloadFileResource::resolveStoredFileSize($data['file_path']);

            if ($size !== null) {
                $data['file_size'] = $size;
            }
        }

        return $data;
    }
}
