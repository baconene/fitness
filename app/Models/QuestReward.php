<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestReward extends Model
{
    protected $fillable = [
        'user_quest_id',
        'reward_type',
        'reward_payload',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_payload' => 'array',
            'granted_at' => 'datetime',
        ];
    }

    public function userQuest()
    {
        return $this->belongsTo(UserQuest::class);
    }
}
