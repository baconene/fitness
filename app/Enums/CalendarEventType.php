<?php

namespace App\Enums;

enum CalendarEventType: string
{
    case Workout = 'workout';
    case Quest = 'quest';
    case BossEncounter = 'boss_encounter';
    case Rest = 'rest';
    case Custom = 'custom';
}
