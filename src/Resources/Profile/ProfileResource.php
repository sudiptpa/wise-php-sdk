<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Profile;

use Theocode\Wise\Resources\Profile\Models\Profile;
use Theocode\Wise\Resources\Resource;
use Theocode\Wise\Support\Collection;

final class ProfileResource extends Resource
{
    /**
     * Implemented from currently available /v2/profiles endpoint.
     *
     * @return Collection<Profile>
     */
    public function list(): Collection
    {
        $payload = $this->client->request('GET', '/v2/profiles');

        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Profile::fromArray($item);
            }
        }

        return new Collection($items);
    }

    public function get(int $profileId): Profile
    {
        $payload = $this->client->request('GET', "/v2/profiles/{$profileId}");

        return Profile::fromArray($payload);
    }
}
