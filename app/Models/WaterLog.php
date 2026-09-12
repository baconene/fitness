<?php

namespace App\Models;

use Database\Factories\WaterLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterLog extends Model
{
    /** @use HasFactory<WaterLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount_ml',
        'logged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
            'amount_ml' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
