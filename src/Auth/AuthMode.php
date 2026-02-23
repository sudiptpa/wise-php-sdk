<?php

declare(strict_types=1);

namespace Sujip\Wise\Auth;

enum AuthMode: string
{
    case ApiToken = 'api_token';
    case OAuth2 = 'oauth2';
}
