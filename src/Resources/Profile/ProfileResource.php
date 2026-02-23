<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Profile;

use Sujip\Wise\Resources\Profile\Models\Profile;
use Sujip\Wise\Resources\Resource;
use Sujip\Wise\Support\Collection;

final class ProfileResource extends Resource
{
    /**
     * Implemented from currently available /v1/profiles endpoint.
     *
     * @return Collection<Profile>
     */
    public function list(): Collection
    {
        $payload = $this->client->request('GET', '/v1/profiles');

        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Profile::fromArray($item);
            }
        }

        return new Collection($items);
    }
}
