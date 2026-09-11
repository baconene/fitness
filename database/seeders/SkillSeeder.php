<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['Power Surge', 'Raises raw force output.', ['strength' => 2], 10],
            ['Second Wind', 'Extends work capacity under fatigue.', ['endurance' => 2], 10],
            ['Quickstep', 'Sharpens movement speed and control.', ['agility' => 2], 10],
            ['Ironclad', 'Hardens the body against wear.', ['vitality' => 2], 10],
            ['Focus', 'Steadies the mind through hard sets.', ['willpower' => 2], 10],
            ['Perfect Form', 'Improves efficiency across every lift.', ['strength' => 1, 'agility' => 1], 5],
            ['Recovery Protocol', 'Speeds recovery between sessions.', ['vitality' => 1, 'endurance' => 1], 5],
        ];

        foreach ($skills as [$name, $description, $bonus, $maxLevel]) {
            Skill::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'name' => $name,
                    'description' => $description,
                    'base_level' => 1,
                    'max_level' => $maxLevel,
                    'stat_bonus_per_level' => $bonus,
                    'is_active' => true,
                ]
            );
        }
    }
}
