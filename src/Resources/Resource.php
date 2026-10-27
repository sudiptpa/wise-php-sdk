<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources;

use Theocode\Wise\WiseClient;

abstract class Resource
{
    protected WiseClient $client;

    public function __construct(WiseClient $client)
    {
        $this->client = $client;
    }
}
