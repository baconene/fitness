<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use App\Models\UserInventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function addItem(User $user, Item $item, int $quantity = 1): UserInventory
    {
        return DB::transaction(function () use ($user, $item, $quantity) {
            $inventory = UserInventory::firstOrCreate(
                ['user_id' => $user->id, 'item_id' => $item->id],
                ['quantity' => 0]
            );

            $inventory->increment('quantity', $quantity);

            return $inventory->fresh();
        });
    }

    public function removeItem(User $user, Item $item, int $quantity = 1): bool
    {
        return DB::transaction(function () use ($user, $item, $quantity) {
            $inventory = UserInventory::where('user_id', $user->id)
                ->where('item_id', $item->id)
                ->first();

            if (! $inventory || $inventory->quantity < $quantity) {
                return false;
            }

            if ($inventory->quantity === $quantity) {
                $inventory->delete();
            } else {
                $inventory->decrement('quantity', $quantity);
            }

            return true;
        });
    }

    public function getInventory(User $user): Collection
    {
        return UserInventory::where('user_id', $user->id)
            ->with('item')
            ->get();
    }

    public function equipItem(User $user, Item $item): bool
    {
        return DB::transaction(function () use ($user, $item) {
            if (! $item->is_equippable || ! $item->is_active || ! $item->equip_slot) {
                return false;
            }

            $inventory = UserInventory::where('user_id', $user->id)
                ->where('item_id', $item->id)
                ->first();

            if (! $inventory || $inventory->quantity < 1) {
                return false;
            }

            $hunterProfile = $user->hunterProfile()->lockForUpdate()->first();
            if (! $hunterProfile) {
                return false;
            }

            $meta = $hunterProfile->avatar_meta?->getArrayCopy() ?? [];
            $meta['equipped_items'][$item->equip_slot] = $item->id;
            $hunterProfile->update(['avatar_meta' => $meta]);

            return true;
        });
    }

    public function unequipItem(User $user, Item $item): bool
    {
        return DB::transaction(function () use ($user, $item) {
            $profile = $user->hunterProfile()->lockForUpdate()->first();
            if (! $profile) {
                return false;
            }

            $meta = $profile->avatar_meta?->getArrayCopy() ?? [];
            if ((int) ($meta['equipped_items'][$item->equip_slot] ?? 0) !== $item->id) {
                return false;
            }

            unset($meta['equipped_items'][$item->equip_slot]);
            $profile->update(['avatar_meta' => $meta]);

            return true;
        });
    }

    public function sellItem(User $user, Item $item, int $quantity = 1): int
    {
        return DB::transaction(function () use ($user, $item, $quantity) {
            $removed = $this->removeItem($user, $item, $quantity);

            if (! $removed) {
                return 0;
            }

            $totalValue = $item->sell_price * $quantity;

            return $totalValue;
        });
    }
}
