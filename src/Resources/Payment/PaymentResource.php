<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Payment;

use Theocode\Wise\Resources\Payment\Models\Payment;
use Theocode\Wise\Resources\Payment\Requests\FundTransferRequest;
use Theocode\Wise\Resources\Resource;

final class PaymentResource extends Resource
{
    public function fundTransfer(int $profileId, int $transferId, FundTransferRequest $request): Payment
    {
        $payload = $this->client->request('POST', "/v3/profiles/{$profileId}/transfers/{$transferId}/payments", body: $request->toArray());

        return Payment::fromArray($payload);
    }
}
