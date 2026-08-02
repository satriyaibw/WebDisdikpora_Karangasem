<?php

namespace App\Rules;

use App\Models\Official;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Pastikan atasan yang dipilih pada pohon pejabat bukan dirinya sendiri
 * maupun keturunannya, sehingga struktur organisasi tidak membentuk siklus.
 */
class OfficialParentRule implements ValidationRule
{
    public function __construct(private readonly ?int $officialId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parentId = (int) $value;

        if ($this->officialId !== null && $parentId === $this->officialId) {
            $fail('Atasan tidak boleh sama dengan pejabat yang bersangkutan.');

            return;
        }

        if ($this->officialId === null) {
            return;
        }

        if (in_array($parentId, $this->collectDescendantIds($this->officialId), true)) {
            $fail('Atasan tidak boleh merupakan bawahan dari pejabat tersebut.');
        }
    }

    /**
     * Kumpulkan seluruh ID keturunan dengan BFS di memori
     * (satu query, aman dari siklus data yang sudah terlanjur ada).
     *
     * @return list<int>
     */
    private function collectDescendantIds(int $officialId): array
    {
        $parentById = Official::query()
            ->whereNotNull('parent_id')
            ->pluck('parent_id', 'id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $descendants = [];
        $queue = [$officialId];

        while ($queue !== []) {
            $current = array_shift($queue);

            foreach ($parentById as $childId => $parentId) {
                if ($parentId !== $current || in_array($childId, $descendants, true)) {
                    continue;
                }

                $descendants[] = $childId;
                $queue[] = $childId;
            }
        }

        return $descendants;
    }
}
