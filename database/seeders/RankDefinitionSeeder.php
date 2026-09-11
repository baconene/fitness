<?php

namespace Database\Seeders;

use App\Models\RankDefinition;
use Illuminate\Database\Seeder;

class RankDefinitionSeeder extends Seeder
{
    /**
     * Level thresholds mirror RankService::$rankThresholds.
     */
    public function run(): void
    {
        $ranks = [
            ['E', 1, 'E-Rank', 'Newly awakened. The system has taken notice.', 1],
            ['D', 10, 'D-Rank', 'The basics are yours. Consistency is the next trial.', 2],
            ['C', 20, 'C-Rank', 'A capable hunter. Harder gates open.', 3],
            ['B', 35, 'B-Rank', 'Strength others rely on.', 4],
            ['A', 55, 'A-Rank', 'Few reach this far. Fewer stay.', 5],
            ['S', 80, 'S-Rank', 'The ceiling is a rumour.', 6],
        ];

        foreach ($ranks as [$rank, $minLevel, $displayName, $description, $sortOrder]) {
            RankDefinition::updateOrCreate(
                ['rank' => $rank],
                [
                    'min_level' => $minLevel,
                    'display_name' => $displayName,
                    'description' => $description,
                    'sort_order' => $sortOrder,
                ]
            );
        }
    }
}
