<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Activity;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Activity\Requests\ListActivitiesRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class ActivityResourceTest extends TestCase
{
    public function testListsActivitiesWithCursorPagination(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/activity_page.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $page = $client->activity()->list(123, new ListActivitiesRequest(status: 'COMPLETED', nextCursor: 'cursor_1', size: 20));

        self::assertTrue($page->hasNext());
        self::assertSame('cursor_2', $page->nextCursor());
        self::assertSame('Transfer completed', $page->activities[0]->titlePlainText());

        $query = $transport->lastRequest()->getUri()->getQuery();
        self::assertStringContainsString('nextCursor=cursor_1', $query);
        self::assertStringContainsString('size=20', $query);
    }

    public function testIteratesAcrossPages(): void
    {
        $first = '{"nextCursor":"cursor_2","activities":[{"status":"COMPLETED","title":"First page","resource":{"id":"1","type":"transfer"}}]}';
        $second = '{"nextCursor":null,"activities":[{"status":"COMPLETED","title":"Second page","resource":{"id":"2","type":"transfer"}}]}';

        $transport = new FakeTransport([
            Psr7Factory::response(200, $first),
            Psr7Factory::response(200, $second),
        ]);
        $client = TestClientFactory::make($transport);

        $titles = [];
        foreach ($client->activity()->iterate(123, new ListActivitiesRequest(size: 1)) as $activity) {
            $titles[] = $activity->titlePlainText();
        }

        self::assertSame(['First page', 'Second page'], $titles);
        self::assertStringContainsString('size=1', $transport->requests()[0]->getUri()->getQuery());
        self::assertStringContainsString('nextCursor=cursor_2', $transport->requests()[1]->getUri()->getQuery());
    }
}
