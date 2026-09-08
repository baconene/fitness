<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Support\Facades\DB;

class SkillService
{
    public function learnSkill(User $user, Skill $skill): UserSkill
    {
        return DB::transaction(function () use ($user, $skill) {
            $userSkill = UserSkill::firstOrCreate(
                ['user_id' => $user->id, 'skill_id' => $skill->id],
                [
                    'level' => $skill->base_level,
                    'experience' => 0,
                    'learned_at' => now(),
                ]
            );

            return $userSkill->fresh();
        });
    }

    public function addSkillExperience(User $user, Skill $skill, int $experience): UserSkill
    {
        return DB::transaction(function () use ($user, $skill, $experience) {
            $userSkill = UserSkill::where('user_id', $user->id)
                ->where('skill_id', $skill->id)
                ->first();

            if (! $userSkill) {
                $userSkill = $this->learnSkill($user, $skill);
            }

            $userSkill->increment('experience', $experience);

            // Auto-level if experience threshold reached
            $levelUpThreshold = 100 * $userSkill->level;
            while ($userSkill->experience >= $levelUpThreshold && $userSkill->level < $skill->max_level) {
                $userSkill->experience -= $levelUpThreshold;
                $userSkill->increment('level');
                $levelUpThreshold = 100 * $userSkill->level;
            }

            return $userSkill->fresh();
        });
    }

    public function levelUpSkill(User $user, Skill $skill): UserSkill
    {
        return DB::transaction(function () use ($user, $skill) {
            $userSkill = UserSkill::where('user_id', $user->id)
                ->where('skill_id', $skill->id)
                ->first();

            if (! $userSkill) {
                return $this->learnSkill($user, $skill);
            }

            if ($userSkill->level < $skill->max_level) {
                $userSkill->increment('level');
            }

            return $userSkill->fresh();
        });
    }

    public function getUserSkills(User $user)
    {
        return UserSkill::where('user_id', $user->id)
            ->with('skill')
            ->get();
    }

    public function getSkillLevel(User $user, Skill $skill): int
    {
        $userSkill = UserSkill::where('user_id', $user->id)
            ->where('skill_id', $skill->id)
            ->first();

        return $userSkill?->level ?? 0;
    }
}
