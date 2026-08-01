<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Auditable, HasFactory;

    /**
     * Model kustom untuk role agar dapat direkam
     * pada tabel audit_logs melalui trait Auditable.
     */

    /**
     * Label Bahasa Indonesia untuk tampilan di panel admin.
     */
    public function getLabelAttribute(): string
    {
        return RolePermissionSeeder::ROLE_LABELS[$this->name] ?? Str::headline($this->name);
    }
}
