<?php

namespace Tests\Feature;

use App\Enums\HunterRank;
use App\Models\Dungeon;
use App\Models\DungeonFloor;
use App\Models\DungeonRun;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Skill;
use App\Models\Title;
use App\Models\User;
use App\Models\UserInventory;
use App\Models\UserSkill;
use App\Models\UserTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2ComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_categories_and_items()
    {
        $category = ItemCategory::create(['name' => 'Armor', 'slug' => 'armor']);
        $item = Item::create([
            'item_category_id' => $category->id,
            'name' => 'Iron Breastplate',
            'slug' => 'iron_breastplate',
            'rarity' => 'common',
            'stat_bonuses' => ['endurance' => 5],
            'sell_price' => 100,
            'is_equippable' => true,
            'equip_slot' => 'chest',
        ]);

        $this->assertDatabaseHas('items', ['name' => 'Iron Breastplate']);
        $this->assertEquals('chest', $item->equip_slot);
    }

    public function test_user_inventory_management()
    {
        $user = User::factory()->create();
        $category = ItemCategory::create(['name' => 'Potion', 'slug' => 'potion']);
        $item = Item::create([
            'item_category_id' => $category->id,
            'name' => 'Health Potion',
            'slug' => 'health_potion',
            'rarity' => 'common',
            'sell_price' => 50,
        ]);

        $inventory = UserInventory::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        $this->assertEquals(5, $inventory->quantity);
        $this->assertDatabaseHas('user_inventory', ['user_id' => $user->id, 'quantity' => 5]);
    }

    public function test_skills_progression()
    {
        $user = User::factory()->create();
        $skill = Skill::create([
            'name' => 'Power Strike',
            'slug' => 'power_strike',
            'description' => 'A powerful melee attack',
            'max_level' => 10,
            'stat_bonus_per_level' => ['strength' => 2],
        ]);

        $userSkill = UserSkill::create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'level' => 1,
            'experience' => 0,
            'learned_at' => now(),
        ]);

        $this->assertEquals(1, $userSkill->level);
        $this->assertDatabaseHas('user_skills', ['user_id' => $user->id]);
    }

    public function test_titles_unlocking()
    {
        $user = User::factory()->create();
        $title = Title::create([
            'name' => 'Champion',
            'slug' => 'champion',
            'rarity' => 'legendary',
        ]);

        $userTitle = UserTitle::create([
            'user_id' => $user->id,
            'title_id' => $title->id,
            'is_active' => true,
            'unlocked_at' => now(),
        ]);

        $this->assertTrue($userTitle->is_active);
        $this->assertDatabaseHas('user_titles', ['is_active' => true]);
    }

    public function test_dungeon_structure()
    {
        $dungeon = Dungeon::create([
            'name' => 'Crystal Cavern',
            'slug' => 'crystal_cavern',
            'floor_count' => 3,
            'difficulty' => 'hard',
            'rank_requirement' => 'B',
            'entry_fee' => 100,
        ]);

        DungeonFloor::create([
            'dungeon_id' => $dungeon->id,
            'floor_number' => 1,
            'boss_name' => 'Crystal Golem',
            'boss_health' => 500,
            'xp_reward' => 200,
            'loot_table' => ['item_id' => 1, 'drop_chance' => 0.5],
        ]);

        $this->assertDatabaseHas('dungeon_floors', ['boss_name' => 'Crystal Golem']);
    }

    public function test_dungeon_run_progression()
    {
        $user = User::factory()->create();
        $profile = $user->hunterProfile()->create(['rank' => HunterRank::ERank]);
        $profile->stats()->create();

        $dungeon = Dungeon::create([
            'name' => 'Test Dungeon',
            'slug' => 'test_dungeon',
            'floor_count' => 2,
            'difficulty' => 'easy',
            'rank_requirement' => 'E',
        ]);

        $run = DungeonRun::create([
            'user_id' => $user->id,
            'dungeon_id' => $dungeon->id,
            'current_floor' => 1,
            'status' => 'Active',
            'total_xp_earned' => 0,
            'started_at' => now(),
        ]);

        $this->assertEquals('Active', $run->status);
        $this->assertEquals(1, $run->current_floor);
    }

    public function test_item_equipping()
    {
        $category = ItemCategory::create(['name' => 'Weapons', 'slug' => 'weapons']);
        $item = Item::create([
            'item_category_id' => $category->id,
            'name' => 'Longsword',
            'slug' => 'longsword',
            'rarity' => 'uncommon',
            'is_equippable' => true,
            'equip_slot' => 'main_hand',
            'stat_bonuses' => ['strength' => 10],
        ]);

        $this->assertTrue($item->is_equippable);
        $this->assertNotNull($item->equip_slot);
    }

    public function test_skill_level_up()
    {
        $user = User::factory()->create();
        $skill = Skill::create([
            'name' => 'Fireball',
            'slug' => 'fireball',
            'max_level' => 5,
        ]);

        $userSkill = UserSkill::create([
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'level' => 1,
            'learned_at' => now(),
        ]);

        $userSkill->update(['level' => 2, 'experience' => 100]);

        $this->assertEquals(2, $userSkill->fresh()->level);
    }
}
