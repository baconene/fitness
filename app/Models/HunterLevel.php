<?php

namespace App\Models;

use Database\Factories\HunterLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HunterLevel extends Model
{
    /** @use HasFactory<HunterLevelFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'level';

    protected $keyType = 'int';

    protected $fillable = [
        'level',
        'required_total_xp',
        'rank_unlocked',
    ];
}
