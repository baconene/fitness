<?php

namespace App\Models;

use Database\Factories\DungeonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dungeon extends Model
{
    /** @use HasFactory<DungeonFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'floor_count', 'difficulty', 'rank_requirement', 'entry_fee', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function floors()
    {
        return $this->hasMany(DungeonFloor::class);
    }

    public function runs()
    {
        return $this->hasMany(DungeonRun::class);
    }
}
