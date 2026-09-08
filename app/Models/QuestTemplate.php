<?php

namespace App\Models;

use Database\Factories\QuestTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestTemplate extends Model
{
    /** @use HasFactory<QuestTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'quest_type',
        'category',
        'difficulty',
        'target_metric',
        'target_value',
        'xp_reward_base',
        'stat_reward',
        'min_rank',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stat_reward' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function userQuests()
    {
        return $this->hasMany(UserQuest::class);
    }
}
