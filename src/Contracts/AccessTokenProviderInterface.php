<?php

declare(strict_types=1);

namespace Sujip\Wise\Contracts;

interface AccessTokenProviderInterface
{
    public function getAccessToken(): string;
}
