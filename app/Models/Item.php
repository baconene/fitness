<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    protected $fillable = ['item_category_id', 'name', 'slug', 'description', 'rarity', 'stat_bonuses', 'sell_price', 'is_equippable', 'equip_slot', 'is_active'];

    protected function casts(): array
    {
        return ['stat_bonuses' => 'array', 'is_equippable' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
}
