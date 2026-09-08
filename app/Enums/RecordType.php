<?php

namespace App\Enums;

enum RecordType: string
{
    case MaxWeight = 'max_weight';
    case MaxReps = 'max_reps';
    case MaxVolume = 'max_volume';
    case BestTime = 'best_time';
    case BestDistance = 'best_distance';
}
