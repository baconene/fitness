<?php

namespace App\Models;

use Database\Factories\ExperienceTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceTransaction extends Model
{
    /** @use HasFactory<ExperienceTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'hunter_profile_id',
        'amount',
        'source_type',
        'source_id',
        'source_type',
        'balance_after',
        'idempotency_key',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
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
