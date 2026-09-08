<?php

namespace App\Models;

use Database\Factories\HealthRestrictionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRestriction extends Model
{
    /** @use HasFactory<HealthRestrictionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restriction_type',
        'body_area',
        'description',
        'severity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
