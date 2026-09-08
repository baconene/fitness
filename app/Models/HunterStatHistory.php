<?php

namespace App\Models;

use App\Enums\StatChangeReason;
use App\Enums\StatType;
use Database\Factories\HunterStatHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HunterStatHistory extends Model
{
    /** @use HasFactory<HunterStatHistoryFactory> */
    use HasFactory;

    protected $table = 'hunter_stat_histories';

    protected $fillable = [
        'hunter_profile_id',
        'stat',
        'previous_value',
        'new_value',
        'delta',
        'reason',
        'source_type',
        'source_id',
    ];

    protected function casts(): array
    {
        return [
            'stat' => StatType::class,
            'reason' => StatChangeReason::class,
        ];
    }

    public function hunterProfile()
    {
        return $this->belongsTo(HunterProfile::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
