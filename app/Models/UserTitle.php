<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTitle extends Model
{
    protected $fillable = ['user_id', 'title_id', 'is_active', 'unlocked_at'];

    protected function casts(): array
    {
        return ['unlocked_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function title()
    {
        return $this->belongsTo(Title::class);
    }
}
