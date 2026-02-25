<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity\Enums;

enum ActivityStatus: string
{
    case RequiresAttention = 'REQUIRES_ATTENTION';
    case InProgress = 'IN_PROGRESS';
    case Upcoming = 'UPCOMING';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
}
