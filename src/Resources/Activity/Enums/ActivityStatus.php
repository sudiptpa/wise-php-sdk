<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity\Enums;

enum ActivityStatus: string
{
    case Completed = 'COMPLETED';
    case Pending = 'PENDING';
    case Cancelled = 'CANCELLED';
    case Failed = 'FAILED';
}
