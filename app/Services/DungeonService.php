<?php

namespace App\Services;

use App\Models\Dungeon;
use App\Models\DungeonRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DungeonService
{
    public function __construct(private ExperienceService $experienceService) {}

    public function startRun(User $user, Dungeon $dungeon): DungeonRun
    {
        return DB::transaction(function () use ($user, $dungeon) {
            $run = DungeonRun::create([
                'user_id' => $user->id,
                'dungeon_id' => $dungeon->id,
                'current_floor' => 1,
                'status' => 'Active',
                'total_xp_earned' => 0,
                'items_looted' => [],
                'started_at' => now(),
            ]);

            return $run;
        });
    }

    public function advanceFloor(DungeonRun $run): DungeonRun
    {
        return DB::transaction(function () use ($run) {
            $dungeon = $run->dungeon;

            if ($run->current_floor < $dungeon->floor_count) {
                $run->increment('current_floor');
            } else {
                $this->completeRun($run);
            }

            return $run->fresh();
        });
    }

    public function completeFloor(DungeonRun $run, int $damage, string $idempotencyKey): DungeonRun
    {
        return DB::transaction(function () use ($run, $idempotencyKey) {
            $floor = $run->dungeon->floors()->where('floor_number', $run->current_floor)->first();

            if (! $floor) {
                return $run;
            }

            // Award XP for floor completion
            $xpKey = "{$idempotencyKey}:xp";
            $this->experienceService->awardXp(
                $run->user->hunterProfile,
                $floor->xp_reward,
                'DungeonFloor',
                $floor,
                $xpKey
            );

            $run->increment('total_xp_earned', $floor->xp_reward);

            // Loot items from the floor
            if ($floor->loot_table) {
                $looted = $this->rollLoot($floor->loot_table);
                if ($looted) {
                    $currentLoot = $run->items_looted ?? [];
                    $currentLoot[] = $looted;
                    $run->update(['items_looted' => $currentLoot]);
                }
            }

            // Advance to next floor or complete the run
            $this->advanceFloor($run);

            return $run->fresh();
        });
    }

    public function completeRun(DungeonRun $run): DungeonRun
    {
        return DB::transaction(function () use ($run) {
            $run->update([
                'status' => 'Completed',
                'ended_at' => now(),
            ]);

            return $run->fresh();
        });
    }

    public function abandonRun(DungeonRun $run): DungeonRun
    {
        return DB::transaction(function () use ($run) {
            $run->update([
                'status' => 'Abandoned',
                'ended_at' => now(),
            ]);

            return $run->fresh();
        });
    }

    public function getUserActiveRun(User $user): ?DungeonRun
    {
        return DungeonRun::where('user_id', $user->id)
            ->where('status', 'Active')
            ->with('dungeon')
            ->first();
    }

    public function getUserRuns(User $user)
    {
        return DungeonRun::where('user_id', $user->id)
            ->with('dungeon')
            ->orderBy('started_at', 'desc')
            ->get();
    }

    private function rollLoot(array $lootTable): ?string
    {
        if (empty($lootTable)) {
            return null;
        }

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($lootTable as $item) {
            $cumulative += $item['chance'] ?? 0;
            if ($random <= $cumulative) {
                return $item['item_id'] ?? null;
            }
        }

        return null;
    }
}
