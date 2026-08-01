<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use Auditable, HasFactory;

    /**
     * Model kustom untuk permission agar dapat direkam
     * pada tabel audit_logs melalui trait Auditable.
     */
}
