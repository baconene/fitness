<?php

namespace App\Enums;

enum StatChangeReason: string
{
    case WorkoutCompletion = 'workout_completion';
    case QuestReward = 'quest_reward';
    case LevelUp = 'level_up';
    case RankPromotion = 'rank_promotion';
    case ManualAdjustment = 'manual_adjustment';
}
