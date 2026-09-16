<?php

namespace App\Models;

use Database\Factories\StepLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepLog extends Model
{
    /** @use HasFactory<StepLogFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'counted_on', 'steps'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['counted_on' => 'date', 'steps' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
