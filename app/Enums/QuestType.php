<?php

namespace App\Enums;

enum QuestType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case OneTime = 'onetime';
    case Hidden = 'hidden';
}
