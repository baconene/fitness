<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DungeonFloor extends Model
{
    protected $fillable = ['dungeon_id', 'floor_number', 'boss_name', 'boss_health', 'xp_reward', 'loot_table'];

    protected function casts(): array
    {
        return ['loot_table' => 'array'];
    }

    public function dungeon()
    {
        return $this->belongsTo(Dungeon::class);
    }
}
