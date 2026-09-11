<?php

namespace Database\Seeders;

use App\Models\Boss;
use App\Models\Dungeon;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class RpgContentSeeder extends Seeder
{
    /**
     * Loot, dungeons and bosses. Idempotent, so it is safe to re-run.
     */
    public function run(): void
    {
        $this->seedItems();
        $this->seedDungeons();
        $this->seedBosses();
    }

    private function seedItems(): void
    {
        $categories = [
            ['Equipment', 'equipment', 'Gear worn into a run.'],
            ['Consumable', 'consumable', 'Single-use aids.'],
            ['Material', 'material', 'Recovered from cleared floors.'],
        ];

        foreach ($categories as [$name, $slug, $description]) {
            ItemCategory::updateOrCreate(['slug' => $slug], compact('name', 'description'));
        }

        $ids = ItemCategory::pluck('id', 'slug');

        // [category, name, rarity, sellPrice, equippable, slot, bonuses, description]
        $items = [
            ['equipment', 'Training Wraps', 'common', 15, true, 'hands', ['strength' => 1], 'Worn cloth that steadies the grip.'],
            ['equipment', 'Weighted Vest', 'uncommon', 60, true, 'chest', ['strength' => 2, 'endurance' => 1], 'Adds honest resistance to every movement.'],
            ['equipment', 'Runner Soles', 'uncommon', 55, true, 'feet', ['agility' => 2], 'Light underfoot, quick off the ground.'],
            ['equipment', 'Focus Band', 'rare', 140, true, 'head', ['willpower' => 3], 'Quiets everything but the next repetition.'],
            ['equipment', 'Ironbound Belt', 'rare', 150, true, 'waist', ['strength' => 3, 'vitality' => 1], 'Braces the spine under heavy load.'],
            ['equipment', 'Aegis Plate', 'epic', 380, true, 'chest', ['vitality' => 4, 'endurance' => 2], 'Absorbs punishment that would end a lesser run.'],
            ['consumable', 'Recovery Draught', 'common', 20, false, null, null, 'Restores a measure of stamina between floors.'],
            ['consumable', 'Clarity Tonic', 'uncommon', 45, false, null, null, 'Sharpens focus for a single encounter.'],
            ['material', 'Gate Fragment', 'common', 10, false, null, null, 'A shard left behind when a floor closes.'],
            ['material', 'Resonant Core', 'rare', 120, false, null, null, 'Hums faintly. Clearly worth something.'],
        ];

        foreach ($items as [$category, $name, $rarity, $price, $equippable, $slot, $bonuses, $description]) {
            Item::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'item_category_id' => $ids[$category],
                    'name' => $name,
                    'description' => $description,
                    'rarity' => $rarity,
                    'stat_bonuses' => $bonuses,
                    'sell_price' => $price,
                    'is_equippable' => $equippable,
                    'equip_slot' => $slot,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedDungeons(): void
    {
        $lootIds = Item::pluck('id', 'slug');

        // [name, rank, floors, difficulty, description]
        $dungeons = [
            ['Hollow Vault', 'E', 3, 'normal', 'A shallow gate. Three floors, forgiving pace.'],
            ['Sunken Arena', 'D', 5, 'hard', 'Five floors of sustained work. Bring endurance.'],
            ['Ashen Spire', 'C', 8, 'brutal', 'Eight floors climbing into thin air.'],
        ];

        foreach ($dungeons as [$name, $rank, $floors, $difficulty, $description]) {
            $dungeon = Dungeon::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'name' => $name,
                    'description' => $description,
                    'floor_count' => $floors,
                    'difficulty' => $difficulty,
                    'rank_requirement' => $rank,
                    'entry_fee' => 0,
                    'is_active' => true,
                ]
            );

            for ($floor = 1; $floor <= $floors; $floor++) {
                $dungeon->floors()->updateOrCreate(
                    ['floor_number' => $floor],
                    [
                        'boss_name' => "{$name} Floor {$floor} Warden",
                        'boss_health' => 100 * $floor,
                        'xp_reward' => 40 * $floor,
                        'loot_table' => [
                            ['item_id' => $lootIds['gate-fragment'], 'chance' => 55],
                            ['item_id' => $lootIds['recovery-draught'], 'chance' => 25],
                            ['item_id' => $lootIds['training-wraps'], 'chance' => 15],
                            ['item_id' => $lootIds['resonant-core'], 'chance' => 5],
                        ],
                    ]
                );
            }
        }
    }

    private function seedBosses(): void
    {
        $bosses = [
            ['Gate Warden', 'E', 400, 200, 'The first thing standing between you and a cleared gate.'],
            ['Stone Revenant', 'D', 900, 450, 'Slow, heavy, and entirely unbothered by your schedule.'],
            ['Ashen Sovereign', 'C', 1800, 900, 'Rules the spire. Has never been in a hurry.'],
        ];

        foreach ($bosses as [$name, $rank, $health, $xp, $description]) {
            Boss::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'name' => $name,
                    'description' => $description,
                    'rank_requirement' => $rank,
                    'max_health' => $health,
                    'xp_reward' => $xp,
                    'workout_template' => ['sets' => 12, 'note' => 'Damage is dealt by completed sets.'],
                    'is_active' => true,
                ]
            );
        }
    }
}
