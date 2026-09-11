<?php

namespace App\Services;

use App\Models\Boss;
use App\Models\BossDamageEvent;
use App\Models\BossEncounter;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BossBattleService
{
    public function __construct(private ExperienceService $experienceService) {}

    public function startEncounter(User $user, Boss $boss): BossEncounter
    {
        return DB::transaction(function () use ($user, $boss) {
            $user->hunterProfile()->lockForUpdate()->first();

            return $user->bossEncounters()->firstOrCreate(
                ['status' => 'Active'],
                ['boss_id' => $boss->id, 'current_health' => $boss->max_health, 'started_at' => now()],
            );
        });
    }

    public function applyDamage(BossEncounter $encounter, int $damageAmount, string $idempotencyKey): int
    {
        return DB::transaction(function () use ($encounter, $damageAmount, $idempotencyKey) {
            $encounter = BossEncounter::whereKey($encounter->id)->lockForUpdate()->firstOrFail();
            if ($encounter->status !== 'Active' || $damageAmount <= 0) {
                return $encounter->current_health;
            }

            $existingEvent = BossDamageEvent::where('boss_encounter_id', $encounter->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent) {
                return $encounter->fresh()->current_health;
            }

            $newHealth = max(0, $encounter->current_health - $damageAmount);
            $encounter->update(['current_health' => $newHealth]);

            BossDamageEvent::create([
                'boss_encounter_id' => $encounter->id,
                'source_type' => 'Workout',
                'damage_amount' => $damageAmount,
                'idempotency_key' => $idempotencyKey,
            ]);

            if ($newHealth <= 0) {
                $this->resolveEncounter($encounter);
            }

            return $newHealth;
        });
    }

    public function resolveEncounter(BossEncounter $encounter): void
    {
        if ($encounter->status !== 'Active' || $encounter->current_health > 0) {
            return;
        }

        $encounter->update([
            'status' => 'Defeated',
            'ended_at' => now(),
        ]);

        $profile = $encounter->user->hunterProfile;
        $this->experienceService->awardXp($profile, $encounter->boss->xp_reward, 'BossDefeated', $encounter, "boss:{$encounter->id}:xp");
    }
}
