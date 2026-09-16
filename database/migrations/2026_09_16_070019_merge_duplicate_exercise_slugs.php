<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Merges exercises left behind by the underscore slug convention.
 *
 * Early seeds wrote slugs like `bench_press`; the seeder now writes
 * `bench-press`, so updateOrCreate produced a second row rather than updating
 * the first. Both are active, so the same exercise appears twice in every
 * picker. References are repointed at the hyphenated row before the legacy one
 * is removed, so no workout or program loses its exercise.
 */
return new class extends Migration
{
    /** Tables holding an exercise_id that must follow the merge. */
    private const REFERENCING_TABLES = [
        'workout_exercises',
        'program_exercises',
        'personal_records',
    ];

    public function up(): void
    {
        foreach ($this->duplicatePairs() as [$legacyId, $canonicalId]) {
            DB::transaction(function () use ($legacyId, $canonicalId): void {
                foreach (self::REFERENCING_TABLES as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'exercise_id')) {
                        DB::table($table)->where('exercise_id', $legacyId)->update(['exercise_id' => $canonicalId]);
                    }
                }

                DB::table('exercises')->where('id', $legacyId)->delete();
            });
        }
    }

    /**
     * Deliberately irreversible: the merged row's identity is gone, and
     * recreating it would reintroduce the duplicate this migration removes.
     */
    public function down(): void {}

    /**
     * Underscore-slug rows that have a hyphenated counterpart.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    private function duplicatePairs(): array
    {
        $bySlug = DB::table('exercises')->pluck('id', 'slug');
        $pairs = [];

        foreach ($bySlug as $slug => $id) {
            if (! str_contains($slug, '_')) {
                continue;
            }

            $canonical = $bySlug[str_replace('_', '-', $slug)] ?? null;

            if ($canonical && $canonical !== $id) {
                $pairs[] = [$id, $canonical];
            }
        }

        return $pairs;
    }
};
