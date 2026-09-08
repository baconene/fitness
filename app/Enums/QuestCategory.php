<?php

namespace App\Enums;

enum QuestCategory: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case Consistency = 'consistency';
    case Volume = 'volume';
    case Custom = 'custom';
}
