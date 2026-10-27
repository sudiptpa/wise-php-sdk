<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Support;

use Theocode\Wise\Config\ClientConfig;
use Theocode\Wise\Wise;
use Theocode\Wise\WiseClient;

final class TestClientFactory
{
    public static function make(FakeTransport $transport, ?ClientConfig $config = null): WiseClient
    {
        $config ??= ClientConfig::apiToken('test-token');
        $factory = new Psr7Factory;

        return Wise::client($config, $transport, $factory, $factory);
    }
}
