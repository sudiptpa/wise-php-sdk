<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Support;

use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Wise;
use Sujip\Wise\WiseClient;

final class TestClientFactory
{
    public static function make(FakeTransport $transport, ?ClientConfig $config = null): WiseClient
    {
        $config ??= ClientConfig::productionApiToken('test-token');
        $factory = new Psr7Factory();

        return Wise::client($config, $transport, $factory, $factory);
    }
}
