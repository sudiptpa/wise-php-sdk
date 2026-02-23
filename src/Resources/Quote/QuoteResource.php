<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote;

use Sujip\Wise\Resources\Quote\Models\Quote;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateUnauthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Resources\Resource;

final class QuoteResource extends Resource
{
    public function createUnauthenticated(CreateUnauthenticatedQuoteRequest $request): Quote
    {
        $payload = $this->client->request('POST', '/v3/quotes', body: $request->toArray(), authenticated: false);

        return Quote::fromArray($payload);
    }

    public function createAuthenticated(int $profileId, CreateAuthenticatedQuoteRequest $request): Quote
    {
        $payload = $this->client->request('POST', "/v3/profiles/{$profileId}/quotes", body: $request->toArray());

        return Quote::fromArray($payload);
    }

    public function get(int $profileId, int $quoteId): Quote
    {
        $payload = $this->client->request('GET', "/v3/profiles/{$profileId}/quotes/{$quoteId}");

        return Quote::fromArray($payload);
    }

    public function update(int $profileId, int $quoteId, UpdateQuoteRequest $request): Quote
    {
        $payload = $this->client->request('PATCH', "/v3/profiles/{$profileId}/quotes/{$quoteId}", body: $request->toArray());

        return Quote::fromArray($payload);
    }
}
