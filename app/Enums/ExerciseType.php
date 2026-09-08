<?php

namespace App\Enums;

enum ExerciseType: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case Mobility = 'mobility';
    case Stretching = 'stretching';
    case Bodyweight = 'bodyweight';
    case Plyometric = 'plyometric';
}
