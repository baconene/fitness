<?php

namespace App\Enums;

enum AchievementCategory: string
{
    case Milestone = 'milestone';
    case Consistency = 'consistency';
    case Strength = 'strength';
    case Boss = 'boss';
    case Special = 'special';
}
