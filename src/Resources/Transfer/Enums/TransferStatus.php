<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer\Enums;

enum TransferStatus: string
{
    case Completed = 'completed';
    case OutgoingPaymentSent = 'outgoing_payment_sent';
    case IncomingPaymentWaiting = 'incoming_payment_waiting';
    case Processing = 'processing';
    case Cancelled = 'cancelled';
    case FundsRefunded = 'funds_refunded';
}
