<?php

namespace App\Models;

use Database\Factories\RankDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankDefinition extends Model
{
    /** @use HasFactory<RankDefinitionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'rank';

    protected $keyType = 'string';

    protected $fillable = [
        'rank',
        'min_level',
        'display_name',
        'description',
        'sort_order',
    ];
}
