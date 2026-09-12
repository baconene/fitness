<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\TrainingProgram;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreshSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_on_an_empty_database(): void
    {
        $this->assertSame(0, User::count());

        $this->seed(DatabaseSeeder::class);

        // This is the branch that invokes UserFactory, and therefore fake().
        $this->assertTrue(User::where('email', 'test@example.com')->exists());
        $this->assertNotEmpty(User::where('email', 'test@example.com')->value('name'));

        $this->assertGreaterThanOrEqual(100, Exercise::count());
        $this->assertGreaterThanOrEqual(8, TrainingProgram::count());
    }
}
