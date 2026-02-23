<?php

declare(strict_types=1);

use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Resources\Activity\Requests\ListActivitiesRequest;
use Sujip\Wise\Transport\Psr18Transport;
use Sujip\Wise\Wise;

require __DIR__ . '/../vendor/autoload.php';

$config = ClientConfig::productionApiToken('your-token');
$transport = new Psr18Transport($yourPsr18Client);
$wise = Wise::client($config, $transport, $yourRequestFactory, $yourStreamFactory);

$page = $wise->activity()->list(123, new ListActivitiesRequest(size: 20));

foreach ($page->activities as $activity) {
    echo $activity->status() . ': ' . $activity->titlePlainText() . PHP_EOL;
}

if ($page->hasNext()) {
    echo 'Next cursor: ' . $page->nextCursor() . PHP_EOL;
}
