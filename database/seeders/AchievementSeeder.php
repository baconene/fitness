<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * criteria_type values must match AchievementService::checkCriteria().
     */
    public function run(): void
    {
        $achievements = [
            ['First Steps', 'Milestone', 'WorkoutsCompleted', 1, 50, 'Complete your first workout.'],
            ['Getting Consistent', 'Milestone', 'WorkoutsCompleted', 10, 150, 'Complete 10 workouts.'],
            ['Dedicated', 'Milestone', 'WorkoutsCompleted', 50, 500, 'Complete 50 workouts.'],
            ['Relentless', 'Milestone', 'WorkoutsCompleted', 100, 1200, 'Complete 100 workouts.'],

            ['Quest Taker', 'Quests', 'QuestsCompleted', 1, 40, 'Complete your first quest.'],
            ['Quest Runner', 'Quests', 'QuestsCompleted', 25, 200, 'Complete 25 quests.'],
            ['Quest Master', 'Quests', 'QuestsCompleted', 100, 800, 'Complete 100 quests.'],

            ['Awakened', 'Progression', 'LevelReached', 2, 60, 'Reach level 2.'],
            ['Rising Hunter', 'Progression', 'LevelReached', 10, 250, 'Reach level 10 and D-Rank.'],
            ['Seasoned Hunter', 'Progression', 'LevelReached', 20, 600, 'Reach level 20 and C-Rank.'],
            ['Elite Hunter', 'Progression', 'LevelReached', 35, 1000, 'Reach level 35 and B-Rank.'],

            ['Spark', 'Experience', 'TotalXpEarned', 500, 50, 'Earn 500 total XP.'],
            ['Ember', 'Experience', 'TotalXpEarned', 5000, 200, 'Earn 5,000 total XP.'],
            ['Wildfire', 'Experience', 'TotalXpEarned', 25000, 750, 'Earn 25,000 total XP.'],

            ['Giant Slayer', 'Combat', 'BossesDefeated', 1, 200, 'Defeat your first boss.'],
            ['Boss Hunter', 'Combat', 'BossesDefeated', 10, 700, 'Defeat 10 bosses.'],
        ];

        foreach ($achievements as [$name, $category, $criteriaType, $criteriaValue, $xp, $description]) {
            Achievement::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'criteria_type' => $criteriaType,
                    'criteria_value' => $criteriaValue,
                    'xp_reward' => $xp,
                    'is_hidden' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}
