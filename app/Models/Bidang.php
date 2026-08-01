<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master Bidang/Sub-Bagian Disdikpora Karangasem (MasterPlan 5.1–5.2).
 *
 * Dipakai bersama oleh Katalog Layanan Publik (services) dan
 * Repositori Dokumen SOP (sop_documents).
 */
class Bidang extends Model
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Layanan publik yang tergabung dalam bidang ini.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'bidang_id');
    }

    /**
     * Dokumen SOP yang tergabung dalam bidang ini.
     */
    public function sopDocuments(): HasMany
    {
        return $this->hasMany(SopDocument::class, 'bidang_id');
    }
}
