<?php

namespace Database\Seeders;

use App\Models\QuestTemplate;
use Illuminate\Database\Seeder;

class QuestTemplateSeeder extends Seeder
{
    /**
     * Mission templates.
     *
     * `target_metric` must be one QuestGenerationService::synchronizeProgress()
     * knows how to compute, otherwise the mission is generated but can never
     * progress. Supported today: WorkoutsCompleted, MeasurementsLogged,
     * PersonalRecords, WaterLitres, ActiveDays, TrainingMinutes, DistanceKm.
     */
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            $template['slug'] = str($template['name'])->slug()->value();
            $template['is_active'] = true;

            QuestTemplate::updateOrCreate(['slug' => $template['slug']], $template);
        }

        $this->retireUnsupportedTemplates();
    }

    /**
     * Steps are not recorded anywhere in the app, so this mission could be
     * assigned but never completed. Deactivating keeps existing rows intact
     * while stopping it from being handed out again.
     */
    private function retireUnsupportedTemplates(): void
    {
        QuestTemplate::whereIn('slug', ['reach-8000-steps'])->update(['is_active' => false]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function templates(): array
    {
        return [
            // ---- Daily ----
            ['name' => 'Complete today’s workout', 'quest_type' => 'Daily', 'category' => 'Training', 'difficulty' => 'Medium', 'target_metric' => 'WorkoutsCompleted', 'target_value' => 1, 'xp_reward_base' => 60, 'stat_reward' => ['willpower' => 1]],
            ['name' => 'Drink 3L of water', 'quest_type' => 'Daily', 'category' => 'Recovery', 'difficulty' => 'Easy', 'target_metric' => 'WaterLitres', 'target_value' => 3, 'xp_reward_base' => 25],
            ['name' => 'Stay hydrated with 2L', 'quest_type' => 'Daily', 'category' => 'Recovery', 'difficulty' => 'Easy', 'target_metric' => 'WaterLitres', 'target_value' => 2, 'xp_reward_base' => 18],
            ['name' => 'Log a body measurement', 'quest_type' => 'Daily', 'category' => 'Tracking', 'difficulty' => 'Easy', 'target_metric' => 'MeasurementsLogged', 'target_value' => 1, 'xp_reward_base' => 20],
            ['name' => 'Train for 30 minutes', 'quest_type' => 'Daily', 'category' => 'Conditioning', 'difficulty' => 'Medium', 'target_metric' => 'TrainingMinutes', 'target_value' => 30, 'xp_reward_base' => 45, 'stat_reward' => ['endurance' => 1]],
            ['name' => 'Cover 3km', 'quest_type' => 'Daily', 'category' => 'Conditioning', 'difficulty' => 'Medium', 'target_metric' => 'DistanceKm', 'target_value' => 3, 'xp_reward_base' => 40, 'stat_reward' => ['endurance' => 1]],

            // ---- Weekly ----
            ['name' => 'Train four times this week', 'quest_type' => 'Weekly', 'category' => 'Training', 'difficulty' => 'Hard', 'target_metric' => 'WorkoutsCompleted', 'target_value' => 4, 'xp_reward_base' => 220, 'stat_reward' => ['strength' => 1, 'endurance' => 1]],
            ['name' => 'Set a new personal record', 'quest_type' => 'Weekly', 'category' => 'Training', 'difficulty' => 'Hard', 'target_metric' => 'PersonalRecords', 'target_value' => 1, 'xp_reward_base' => 180, 'stat_reward' => ['strength' => 2]],
            ['name' => 'Show up three separate days', 'quest_type' => 'Weekly', 'category' => 'Discipline', 'difficulty' => 'Medium', 'target_metric' => 'ActiveDays', 'target_value' => 3, 'xp_reward_base' => 160, 'stat_reward' => ['willpower' => 2]],
            ['name' => 'Accumulate 150 training minutes', 'quest_type' => 'Weekly', 'category' => 'Conditioning', 'difficulty' => 'Hard', 'target_metric' => 'TrainingMinutes', 'target_value' => 150, 'xp_reward_base' => 200, 'stat_reward' => ['endurance' => 2]],
            ['name' => 'Cover 15km this week', 'quest_type' => 'Weekly', 'category' => 'Conditioning', 'difficulty' => 'Hard', 'target_metric' => 'DistanceKm', 'target_value' => 15, 'xp_reward_base' => 190, 'stat_reward' => ['agility' => 2]],
            ['name' => 'Track your weight twice', 'quest_type' => 'Weekly', 'category' => 'Tracking', 'difficulty' => 'Easy', 'target_metric' => 'MeasurementsLogged', 'target_value' => 2, 'xp_reward_base' => 90],
            ['name' => 'Hit 14L of water', 'quest_type' => 'Weekly', 'category' => 'Recovery', 'difficulty' => 'Medium', 'target_metric' => 'WaterLitres', 'target_value' => 14, 'xp_reward_base' => 140, 'stat_reward' => ['vitality' => 1]],
            ['name' => 'Break two personal records', 'quest_type' => 'Weekly', 'category' => 'Training', 'difficulty' => 'Hard', 'target_metric' => 'PersonalRecords', 'target_value' => 2, 'xp_reward_base' => 260, 'stat_reward' => ['strength' => 3]],
        ];
    }
}
