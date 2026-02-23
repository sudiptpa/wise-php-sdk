<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer;

use Sujip\Wise\Resources\Resource;
use Sujip\Wise\Resources\Transfer\Models\Transfer;
use Sujip\Wise\Resources\Transfer\Models\TransferRequirements;
use Sujip\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Sujip\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;

final class TransferResource extends Resource
{
    public function create(CreateTransferRequest $request): Transfer
    {
        $payload = $this->client->request('POST', '/v1/transfers', body: $request->toArray());

        return Transfer::fromArray($payload);
    }

    public function get(int $transferId): Transfer
    {
        $payload = $this->client->request('GET', "/v1/transfers/{$transferId}");

        return Transfer::fromArray($payload);
    }

    public function requirements(TransferRequirementsRequest $request): TransferRequirements
    {
        $payload = $this->client->request('POST', '/v1/transfer-requirements', body: $request->toArray());

        return TransferRequirements::fromArray($payload);
    }
}
