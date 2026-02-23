<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount;

use Sujip\Wise\Resources\RecipientAccount\Collections\RecipientAccountCollection;
use Sujip\Wise\Resources\RecipientAccount\Models\RecipientAccount;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Resources\Resource;

final class RecipientAccountResource extends Resource
{
    public function create(CreateRecipientAccountRequest $request): RecipientAccount
    {
        $payload = $this->client->request('POST', '/v1/accounts', body: $request->toArray());

        return RecipientAccount::fromArray($payload);
    }

    public function list(?int $profileId = null): RecipientAccountCollection
    {
        $payload = $this->client->request('GET', '/v1/accounts', query: ['profile' => $profileId]);
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = RecipientAccount::fromArray($item);
            }
        }

        return new RecipientAccountCollection($items);
    }

    public function get(int $accountId): RecipientAccount
    {
        $payload = $this->client->request('GET', "/v1/accounts/{$accountId}");

        return RecipientAccount::fromArray($payload);
    }
}
