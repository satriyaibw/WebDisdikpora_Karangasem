<?php

namespace Database\Seeders;

use App\Models\RelatedLink;
use Illuminate\Database\Seeder;

class RelatedLinkSeeder extends Seeder
{
    /**
     * Seed data awal tautan terkait footer secara idempotent.
     */
    public function run(): void
    {
        RelatedLink::updateOrCreate(
            ['name' => 'SP4N-LAPOR!'],
            [
                'url' => 'https://www.lapor.go.id',
                'sort_order' => 0,
                'is_active' => true,
            ]
        );
    }
}
