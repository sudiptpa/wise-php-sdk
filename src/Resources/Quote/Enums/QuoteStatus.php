<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Enums;

enum QuoteStatus: string
{
    case Pending = 'PENDING';
    case Accepted = 'ACCEPTED';
    case Funded = 'FUNDED';
    case Expired = 'EXPIRED';
}
