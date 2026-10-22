<?php

namespace App\Enums;

enum FeatureRequestStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
