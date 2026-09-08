<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestProgress extends Model
{
    protected $fillable = [
        'user_quest_id',
        'current_value',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
        ];
    }

    public function userQuest()
    {
        return $this->belongsTo(UserQuest::class);
    }
}
