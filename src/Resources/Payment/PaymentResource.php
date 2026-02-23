<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Payment;

use Sujip\Wise\Resources\Payment\Models\Payment;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Resource;

final class PaymentResource extends Resource
{
    public function fundTransfer(int $profileId, int $transferId, FundTransferRequest $request): Payment
    {
        $payload = $this->client->request('POST', "/v3/profiles/{$profileId}/transfers/{$transferId}/payments", body: $request->toArray());

        return Payment::fromArray($payload);
    }
}
