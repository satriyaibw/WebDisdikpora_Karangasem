<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus file lama dari disk public saat record dihapus atau
 * saat file diganti lewat proses update (mencegah file orphan).
 *
 * Model yang memakai trait ini wajib mendeklarasikan properti
 * `protected array $fileAttributes = ['nama_kolom', ...];`.
 */
trait DeletesOrphanedFiles
{
    public static function bootDeletesOrphanedFiles(): void
    {
        static::updating(static function (Model $model): void {
            foreach ($model->fileAttributes() as $attribute) {
                $old = $model->getOriginal($attribute);
                $new = $model->getAttribute($attribute);

                if (is_string($old) && $old !== '' && $old !== $new) {
                    static::deleteStoredFile($old);
                }
            }
        });

        static::deleting(static function (Model $model): void {
            foreach ($model->fileAttributes() as $attribute) {
                static::deleteStoredFile($model->getAttribute($attribute));
            }
        });
    }

    /**
     * Daftar kolom yang menyimpan path file di disk `public`.
     *
     * @return array<int, string>
     */
    protected function fileAttributes(): array
    {
        return $this->fileAttributes ?? [];
    }

    protected static function deleteStoredFile(?string $path): void
    {
        if (is_string($path) && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
