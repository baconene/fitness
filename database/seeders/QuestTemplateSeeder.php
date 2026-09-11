<?php

namespace Database\Seeders;

use App\Models\QuestTemplate;
use Illuminate\Database\Seeder;

class QuestTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // --- Daily ---
            [
                'name' => 'Complete today’s workout',
                'quest_type' => 'Daily',
                'category' => 'Training',
                'difficulty' => 'Medium',
                'target_metric' => 'WorkoutsCompleted',
                'target_value' => 1,
                'xp_reward_base' => 60,
                'stat_reward' => ['willpower' => 1],
            ],
            [
                'name' => 'Drink 3L of water',
                'quest_type' => 'Daily',
                'category' => 'Recovery',
                'difficulty' => 'Easy',
                'target_metric' => 'WaterLitres',
                'target_value' => 3,
                'xp_reward_base' => 25,
            ],
            [
                'name' => 'Log a body measurement',
                'quest_type' => 'Daily',
                'category' => 'Tracking',
                'difficulty' => 'Easy',
                'target_metric' => 'MeasurementsLogged',
                'target_value' => 1,
                'xp_reward_base' => 20,
            ],
            [
                'name' => 'Reach 8,000 steps',
                'quest_type' => 'Daily',
                'category' => 'Conditioning',
                'difficulty' => 'Medium',
                'target_metric' => 'Steps',
                'target_value' => 8000,
                'xp_reward_base' => 40,
                'stat_reward' => ['endurance' => 1],
            ],

            // --- Weekly ---
            [
                'name' => 'Train four times this week',
                'quest_type' => 'Weekly',
                'category' => 'Training',
                'difficulty' => 'Hard',
                'target_metric' => 'WorkoutsCompleted',
                'target_value' => 4,
                'xp_reward_base' => 220,
                'stat_reward' => ['strength' => 1, 'endurance' => 1],
            ],
            [
                'name' => 'Set a new personal record',
                'quest_type' => 'Weekly',
                'category' => 'Training',
                'difficulty' => 'Hard',
                'target_metric' => 'PersonalRecords',
                'target_value' => 1,
                'xp_reward_base' => 180,
                'stat_reward' => ['strength' => 2],
            ],
        ];

        foreach ($templates as $template) {
            $template['slug'] = str($template['name'])->slug()->value();
            $template['is_active'] = true;

            QuestTemplate::updateOrCreate(['slug' => $template['slug']], $template);
        }
    }
}
