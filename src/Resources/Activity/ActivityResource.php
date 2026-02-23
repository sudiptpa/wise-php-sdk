<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity;

use Generator;
use Sujip\Wise\Resources\Activity\Models\Activity;
use Sujip\Wise\Resources\Activity\Models\ActivityPage;
use Sujip\Wise\Resources\Activity\Requests\ListActivitiesRequest;
use Sujip\Wise\Resources\Resource;

final class ActivityResource extends Resource
{
    public function list(int $profileId, ?ListActivitiesRequest $request = null): ActivityPage
    {
        $payload = $this->client->request(
            'GET',
            "/v1/profiles/{$profileId}/activities",
            query: $request?->toQuery() ?? [],
        );

        return ActivityPage::fromArray($payload);
    }

    /**
     * @return Generator<int, Activity>
     */
    public function iterate(int $profileId, ?ListActivitiesRequest $request = null): Generator
    {
        $query = $request ?? new ListActivitiesRequest();

        while (true) {
            $page = $this->list($profileId, $query);
            foreach ($page->activities as $activity) {
                yield $activity;
            }

            if (!$page->hasNext()) {
                return;
            }

            $query = $query->withNextCursor($page->nextCursor());
        }
    }
}
