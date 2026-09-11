<?php

namespace Database\Seeders;

use App\Models\Title;
use Illuminate\Database\Seeder;

class TitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            ['The Awakened', 'common', 'Answered the system’s first call.'],
            ['Iron Willed', 'common', 'Held a seven-day streak.'],
            ['Dawn Riser', 'uncommon', 'Trained before sunrise ten times.'],
            ['Unbroken', 'uncommon', 'Held a thirty-day streak.'],
            ['Gate Breaker', 'rare', 'Cleared a dungeon without retreating.'],
            ['Recordsmith', 'rare', 'Set twenty-five personal records.'],
            ['Monarch of Discipline', 'legendary', 'Held a full-year streak.'],
        ];

        foreach ($titles as [$name, $rarity, $description]) {
            Title::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'name' => $name,
                    'description' => $description,
                    'rarity' => $rarity,
                    'is_active' => true,
                ]
            );
        }
    }
}
