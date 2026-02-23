<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources;

use Sujip\Wise\WiseClient;

abstract class Resource
{
    protected WiseClient $client;

    public function __construct(WiseClient $client)
    {
        $this->client = $client;
    }
}
