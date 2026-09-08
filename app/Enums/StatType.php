<?php

namespace App\Enums;

enum StatType: string
{
    case Strength = 'strength';
    case Endurance = 'endurance';
    case Agility = 'agility';
    case Vitality = 'vitality';
    case Willpower = 'willpower';
}
