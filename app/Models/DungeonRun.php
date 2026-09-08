<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DungeonRun extends Model
{
    protected $fillable = ['user_id', 'dungeon_id', 'current_floor', 'status', 'total_xp_earned', 'items_looted', 'started_at', 'ended_at'];

    protected function casts(): array
    {
        return ['items_looted' => 'array', 'started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dungeon()
    {
        return $this->belongsTo(Dungeon::class);
    }
}
