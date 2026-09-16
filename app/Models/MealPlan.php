<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealPlan extends Model
{
    use HasFactory;

    /** The slots a day is divided into, in the order they are eaten. */
    public const MEALS = ['breakfast', 'lunch', 'dinner', 'snack'];

    protected $fillable = ['user_id', 'plan_date', 'logged_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['plan_date' => 'date', 'logged_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MealPlanItem::class)->orderBy('position')->orderBy('id');
    }
}
