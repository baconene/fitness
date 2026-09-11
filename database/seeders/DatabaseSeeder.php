<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reference data, seeded in FK-dependency order. Every seeder is
        // idempotent (updateOrCreate) so this is safe to re-run.
        $this->call([
            RankDefinitionSeeder::class,
            ExerciseCategorySeeder::class,
            ExerciseSeeder::class,
            QuestTemplateSeeder::class,
            AchievementSeeder::class,
            TitleSeeder::class,
            SkillSeeder::class,
            RpgContentSeeder::class,
            TrainingProgramSeeder::class,
        ]);

        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
