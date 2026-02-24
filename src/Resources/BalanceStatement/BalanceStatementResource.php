<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\BalanceStatement;

use Sujip\Wise\Resources\BalanceStatement\Models\BalanceStatement;
use Sujip\Wise\Resources\BalanceStatement\Requests\GetBalanceStatementRequest;
use Sujip\Wise\Resources\Resource;

final class BalanceStatementResource extends Resource
{
    public function getJson(int $profileId, int $balanceId, ?GetBalanceStatementRequest $request = null): BalanceStatement
    {
        $payload = $this->client->request(
            'GET',
            "/v1/profiles/{$profileId}/balance-statements/{$balanceId}/statement.json",
            query: $request?->toQuery() ?? [],
        );

        return BalanceStatement::fromArray($payload);
    }
}
