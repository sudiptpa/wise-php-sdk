<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Transfer;

use Generator;
use Theocode\Wise\Resources\Resource;
use Theocode\Wise\Resources\Transfer\Collections\TransferCollection;
use Theocode\Wise\Resources\Transfer\Models\Transfer;
use Theocode\Wise\Resources\Transfer\Models\TransferRequirements;
use Theocode\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Theocode\Wise\Resources\Transfer\Requests\ListTransfersRequest;
use Theocode\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;

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

    public function list(ListTransfersRequest $request): TransferCollection
    {
        $payload = $this->client->request('GET', '/v1/transfers', query: $request->toQuery());

        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Transfer::fromArray($item);
            }
        }

        return new TransferCollection($items);
    }

    /**
     * @return Generator<int, Transfer>
     */
    public function iterate(ListTransfersRequest $request, int $pageSize = 100): Generator
    {
        $offset = 0;

        while (true) {
            $page = $this->list($request->withOffset($offset)->withLimit($pageSize));
            foreach ($page as $transfer) {
                yield $transfer;
            }

            $count = $page->count();
            if ($count < $pageSize) {
                return;
            }

            $offset += $count;
        }
    }

    public function cancel(int $transferId): Transfer
    {
        $payload = $this->client->request('PUT', "/v1/transfers/{$transferId}/cancel");

        return Transfer::fromArray($payload);
    }

    public function requirements(TransferRequirementsRequest $request): TransferRequirements
    {
        $payload = $this->client->request('POST', '/v1/transfer-requirements', body: $request->toArray());

        return TransferRequirements::fromArray($payload);
    }
}
