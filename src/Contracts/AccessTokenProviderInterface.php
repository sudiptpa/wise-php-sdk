<?php

declare(strict_types=1);

namespace Theocode\Wise\Contracts;

interface AccessTokenProviderInterface
{
    public function getAccessToken(): string;
}
