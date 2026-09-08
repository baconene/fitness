<?php

namespace App\Enums;

enum CalendarStatusIcon: string
{
    case Completed = 'completed';
    case Missed = 'missed';
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Rest = 'rest';

    public function icon(): string
    {
        return match ($this) {
            self::Completed => '✓',
            self::Missed => '✗',
            self::Planned => '●',
            self::InProgress => '⟳',
            self::Rest => 'R',
        };
    }
}
