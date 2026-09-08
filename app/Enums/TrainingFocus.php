<?php

namespace App\Enums;

enum TrainingFocus: string
{
    case Strength = 'strength';
    case Hypertrophy = 'hypertrophy';
    case Endurance = 'endurance';
    case GeneralFitness = 'general_fitness';
    case FatLoss = 'fat_loss';
}
