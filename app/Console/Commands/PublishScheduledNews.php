<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

/**
 * Terbitkan berita terjadwal yang sudah mencapai waktu publikasinya
 * (MasterPlan 3.1 — Scheduled Publishing).
 *
 * Dijalankan otomatis oleh scheduler Laravel setiap menit
 * (docker-compose queue-worker menjalankan `schedule:work`).
 */
class PublishScheduledNews extends Command
{
    protected $signature = 'news:publish-scheduled';

    protected $description = 'Terbitkan berita terjadwal yang sudah mencapai waktu publikasinya';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $news = News::query()
            ->where('status', News::STATUS_SCHEDULED)
            ->where('published_at', '<=', now())
            ->get();

        $published = 0;

        foreach ($news as $item) {
            $item->status = News::STATUS_PUBLISHED;
            $item->save();
            $published++;
        }

        $this->info("Berita yang diterbitkan: {$published}");

        return self::SUCCESS;
    }
}
