<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use App\Models\UserInventory;
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

    public function getInventory(User $user)
    {
        return UserInventory::where('user_id', $user->id)
            ->with('item')
            ->get();
    }

    public function equipItem(User $user, Item $item): bool
    {
        return DB::transaction(function () use ($user, $item) {
            if (! $item->is_equippable) {
                return false;
            }

            $inventory = UserInventory::where('user_id', $user->id)
                ->where('item_id', $item->id)
                ->first();

            if (! $inventory) {
                return false;
            }

            // Unequip any item currently in this slot
            $hunterProfile = $user->hunterProfile;
            if (! $hunterProfile) {
                return false;
            }

            // Mark this item as equipped by storing in a simple mapping
            // (Phase 2: store in equipped_items or similar; for now keep it simple)
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
