<?php

namespace App\Enums;

enum HunterRank: string
{
    case ERank = 'E';
    case DRank = 'D';
    case CRank = 'C';
    case BRank = 'B';
    case ARank = 'A';
    case SRank = 'S';

    public function label(): string
    {
        return match ($this) {
            self::ERank => 'E-Rank',
            self::DRank => 'D-Rank',
            self::CRank => 'C-Rank',
            self::BRank => 'B-Rank',
            self::ARank => 'A-Rank',
            self::SRank => 'S-Rank',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::ERank => 1,
            self::DRank => 2,
            self::CRank => 3,
            self::BRank => 4,
            self::ARank => 5,
            self::SRank => 6,
        };
    }
}
