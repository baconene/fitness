<?php

namespace App\Enums;

enum GoalType: string
{
    case WeightLoss = 'weight_loss';
    case MuscleGain = 'muscle_gain';
    case StrengthGain = 'strength_gain';
    case Endurance = 'endurance';
    case GeneralFitness = 'general_fitness';
    case BodyRecomposition = 'body_recomposition';
    case Custom = 'custom';
}
