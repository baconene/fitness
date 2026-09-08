<?php

namespace App\Models;

use Database\Factories\HealthMeasurementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthMeasurement extends Model
{
    /** @use HasFactory<HealthMeasurementFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'measured_at',
        'weight_kg',
        'body_fat_pct',
        'height_cm',
        'resting_heart_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'measured_at' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
