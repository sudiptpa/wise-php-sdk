<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount;

use Generator;
use Theocode\Wise\Resources\RecipientAccount\Collections\AccountRequirementCollection;
use Theocode\Wise\Resources\RecipientAccount\Collections\RecipientAccountCollection;
use Theocode\Wise\Resources\RecipientAccount\Models\AccountRequirement;
use Theocode\Wise\Resources\RecipientAccount\Models\RecipientAccount;
use Theocode\Wise\Resources\RecipientAccount\Models\RecipientAccountPage;
use Theocode\Wise\Resources\RecipientAccount\Models\RecipientAccountV2;
use Theocode\Wise\Resources\RecipientAccount\Requests\AccountRequirementsOptions;
use Theocode\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Theocode\Wise\Resources\RecipientAccount\Requests\ListRecipientAccountsRequest;
use Theocode\Wise\Resources\Resource;

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

    public function listV2(ListRecipientAccountsRequest $request): RecipientAccountPage
    {
        $payload = $this->client->request('GET', '/v2/accounts', query: $request->toQuery());

        return RecipientAccountPage::fromArray($payload);
    }

    /**
     * @return Generator<int, RecipientAccountV2>
     */
    public function iterateV2(?ListRecipientAccountsRequest $request = null): Generator
    {
        $query = $request ?? new ListRecipientAccountsRequest;

        while (true) {
            $page = $this->listV2($query);
            foreach ($page->content as $recipient) {
                yield $recipient;
            }

            if (! $page->hasNext()) {
                return;
            }

            $query = $query->withSeekPosition($page->nextSeekPosition());
        }
    }

    public function getV2(int $accountId): RecipientAccountV2
    {
        $payload = $this->client->request('GET', "/v2/accounts/{$accountId}");

        return RecipientAccountV2::fromArray($payload);
    }

    public function deactivate(int $accountId): RecipientAccountV2
    {
        $payload = $this->client->request('DELETE', "/v2/accounts/{$accountId}");

        return RecipientAccountV2::fromArray($payload);
    }

    public function requirementsForQuote(string $quoteId, ?AccountRequirementsOptions $o = null): AccountRequirementCollection
    {
        $payload = $this->client->request(
            'GET',
            "/v1/quotes/{$quoteId}/account-requirements",
            query: $o?->toQuery() ?? [],
            headers: ['Accept-Minor-Version' => '1'],
        );

        return $this->requirements($payload);
    }

    /**
     * @param  CreateRecipientAccountRequest|array<string, mixed>  $payload
     */
    public function refreshRequirementsForQuote(string $quoteId, CreateRecipientAccountRequest|array $payload, ?AccountRequirementsOptions $o = null): AccountRequirementCollection
    {
        $body = $payload instanceof CreateRecipientAccountRequest ? $payload->toArray() : $payload;

        $response = $this->client->request(
            'POST',
            "/v1/quotes/{$quoteId}/account-requirements",
            query: $o?->toQuery() ?? [],
            body: $body,
            headers: ['Accept-Minor-Version' => '1'],
        );

        return $this->requirements($response);
    }

    public function requirementsForRoute(string $source, string $target, float $sourceAmount, ?AccountRequirementsOptions $o = null): AccountRequirementCollection
    {
        $query = $o?->toQuery() ?? [];
        $query['source'] = strtoupper($source);
        $query['target'] = strtoupper($target);
        $query['sourceAmount'] = $sourceAmount;

        $payload = $this->client->request(
            'GET',
            '/v1/account-requirements',
            query: $query,
            headers: ['Accept-Minor-Version' => '1'],
        );

        return $this->requirements($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requirements(array $payload): AccountRequirementCollection
    {
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = AccountRequirement::fromArray($item);
            }
        }

        return new AccountRequirementCollection($items);
    }
}
