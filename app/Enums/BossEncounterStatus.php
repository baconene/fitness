<?php

namespace App\Enums;

enum BossEncounterStatus: string
{
    case InProgress = 'in_progress';
    case Victory = 'victory';
    case Defeat = 'defeat';
    case Abandoned = 'abandoned';
}
