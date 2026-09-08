<?php

namespace App\Services;

use App\Models\Title;
use App\Models\User;
use App\Models\UserTitle;
use Illuminate\Support\Facades\DB;

class TitleService
{
    public function unlockTitle(User $user, Title $title): UserTitle
    {
        return DB::transaction(function () use ($user, $title) {
            $userTitle = UserTitle::firstOrCreate(
                ['user_id' => $user->id, 'title_id' => $title->id],
                [
                    'is_active' => false,
                    'unlocked_at' => now(),
                ]
            );

            return $userTitle->fresh();
        });
    }

    public function activateTitle(User $user, Title $title): UserTitle
    {
        return DB::transaction(function () use ($user, $title) {
            $userTitle = UserTitle::where('user_id', $user->id)
                ->where('title_id', $title->id)
                ->first();

            if (! $userTitle) {
                $userTitle = $this->unlockTitle($user, $title);
            }

            // Deactivate all other titles
            UserTitle::where('user_id', $user->id)
                ->where('title_id', '!=', $title->id)
                ->update(['is_active' => false]);

            // Activate this title
            $userTitle->update(['is_active' => true]);

            return $userTitle->fresh();
        });
    }

    public function deactivateTitle(User $user, Title $title): bool
    {
        return DB::transaction(function () use ($user, $title) {
            $userTitle = UserTitle::where('user_id', $user->id)
                ->where('title_id', $title->id)
                ->first();

            if (! $userTitle) {
                return false;
            }

            $userTitle->update(['is_active' => false]);

            return true;
        });
    }

    public function getUserTitles(User $user)
    {
        return UserTitle::where('user_id', $user->id)
            ->with('title')
            ->get();
    }

    public function getActiveTitle(User $user): ?UserTitle
    {
        return UserTitle::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('title')
            ->first();
    }

    public function hasTitle(User $user, Title $title): bool
    {
        return UserTitle::where('user_id', $user->id)
            ->where('title_id', $title->id)
            ->exists();
    }
}
