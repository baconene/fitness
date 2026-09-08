<?php

namespace App\Models;

use Database\Factories\UserQuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuest extends Model
{
    /** @use HasFactory<UserQuestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quest_template_id',
        'status',
        'assigned_date',
        'expires_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'expires_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questTemplate()
    {
        return $this->belongsTo(QuestTemplate::class);
    }

    public function progress()
    {
        return $this->hasOne(QuestProgress::class);
    }

    public function rewards()
    {
        return $this->hasMany(QuestReward::class);
    }
}
