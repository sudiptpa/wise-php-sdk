<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Address;

use Sujip\Wise\Resources\Address\Collections\AddressCollection;
use Sujip\Wise\Resources\Address\Collections\AddressRequirementCollection;
use Sujip\Wise\Resources\Address\Models\Address;
use Sujip\Wise\Resources\Address\Models\AddressRequirement;
use Sujip\Wise\Resources\Address\Requests\CreateAddressRequest;
use Sujip\Wise\Resources\Address\Requests\ResolveAddressRequirementsRequest;
use Sujip\Wise\Resources\Resource;

final class AddressResource extends Resource
{
    public function create(CreateAddressRequest $request): Address
    {
        $payload = $this->client->request('POST', '/v1/addresses', body: $request->toArray());

        return Address::fromArray($payload);
    }

    public function list(?int $profileId = null): AddressCollection
    {
        $payload = $this->client->request('GET', '/v1/addresses', query: ['profileId' => $profileId]);
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Address::fromArray($item);
            }
        }

        return new AddressCollection($items);
    }

    public function get(int $addressId): Address
    {
        $payload = $this->client->request('GET', "/v1/addresses/{$addressId}");

        return Address::fromArray($payload);
    }

    public function requirements(?ResolveAddressRequirementsRequest $request = null): AddressRequirementCollection
    {
        if ($request === null) {
            $payload = $this->client->request('GET', '/v1/address-requirements');
        } else {
            $payload = $this->client->request('POST', '/v1/address-requirements', body: $request->toArray());
        }

        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = AddressRequirement::fromArray($item);
            }
        }

        return new AddressRequirementCollection($items);
    }
}
