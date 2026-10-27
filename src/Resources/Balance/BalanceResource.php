<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Balance;

use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Resources\Balance\Collections\BalanceCollection;
use Theocode\Wise\Resources\Balance\Models\Balance;
use Theocode\Wise\Resources\Balance\Models\BalanceCapacity;
use Theocode\Wise\Resources\Balance\Models\BalanceMovement;
use Theocode\Wise\Resources\Balance\Models\ExcessMoneyAccount;
use Theocode\Wise\Resources\Balance\Models\TotalFunds;
use Theocode\Wise\Resources\Balance\Requests\AddExcessMoneyAccountRequest;
use Theocode\Wise\Resources\Balance\Requests\CreateBalanceMovementRequest;
use Theocode\Wise\Resources\Balance\Requests\CreateBalanceRequest;
use Theocode\Wise\Resources\Resource;

final class BalanceResource extends Resource
{
    public function create(int $profileId, CreateBalanceRequest $request): Balance
    {
        $payload = $this->client->request('POST', "/v4/profiles/{$profileId}/balances", body: $request->toArray());

        return Balance::fromArray($payload);
    }

    public function list(int $profileId, ?string $types = null): BalanceCollection
    {
        $payload = $this->client->request('GET', "/v4/profiles/{$profileId}/balances", query: ['types' => $types]);
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Balance::fromArray($item);
            }
        }

        return new BalanceCollection($items);
    }

    public function get(int $profileId, int $balanceId): Balance
    {
        $payload = $this->client->request('GET', "/v4/profiles/{$profileId}/balances/{$balanceId}");

        return Balance::fromArray($payload);
    }

    public function close(int $profileId, int $balanceId): Balance
    {
        $payload = $this->client->request('DELETE', "/v4/profiles/{$profileId}/balances/{$balanceId}");

        return Balance::fromArray($payload);
    }

    public function move(int $profileId, CreateBalanceMovementRequest $request): BalanceMovement
    {
        $payload = $this->client->request('POST', "/v2/profiles/{$profileId}/balance-movements", body: $request->toArray());

        return BalanceMovement::fromArray($payload);
    }

    public function capacity(int $profileId): BalanceCapacity
    {
        $payload = $this->client->request('GET', "/v1/profiles/{$profileId}/balance-capacity");

        return BalanceCapacity::fromArray($payload);
    }

    public function addExcessMoneyAccount(int $profileId, AddExcessMoneyAccountRequest $request): ExcessMoneyAccount
    {
        $payload = $this->client->request('POST', "/v1/profiles/{$profileId}/excess-money-account", body: $request->toArray());

        return ExcessMoneyAccount::fromArray($payload);
    }

    public function totalFunds(int $profileId, string $currency): TotalFunds
    {
        if (preg_match('/^[A-Z]{3}$/i', $currency) !== 1) {
            throw new ValidationException('currency must be a valid 3-letter currency code.');
        }

        $normalizedCurrency = strtoupper($currency);
        $payload = $this->client->request('GET', "/v1/profiles/{$profileId}/total-funds/{$normalizedCurrency}");

        return TotalFunds::fromArray($payload);
    }
}
