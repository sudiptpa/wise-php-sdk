<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer\Enums;

enum TransferStatus: string
{
    case OutgoingPaymentSent = 'outgoing_payment_sent';
    case IncomingPaymentWaiting = 'incoming_payment_waiting';
    case IncomingPaymentInitiated = 'incoming_payment_initiated';
    case Processing = 'processing';
    case FundsConverted = 'funds_converted';
    case Cancelled = 'cancelled';
    case FundsRefunded = 'funds_refunded';
    case BouncedBack = 'bounced_back';
    case ChargedBack = 'charged_back';
    case Unknown = 'unknown';
}
