<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'base_level', 'max_level', 'stat_bonus_per_level', 'is_active'];

    protected function casts(): array
    {
        return ['stat_bonus_per_level' => 'array', 'is_active' => 'boolean'];
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }
}
