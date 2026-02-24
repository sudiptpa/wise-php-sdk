<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Payment\Enums;

enum PaymentType: string
{
    case Balance = 'BALANCE';
    case BankTransfer = 'BANK_TRANSFER';
    case Swift = 'SWIFT';
    case ManualBankTransfer = 'MANUAL_BANK_TRANSFER';
}
