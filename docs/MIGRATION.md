# Migration Guide

This document covers non-breaking migration paths for SDK API changes.

## Config constructor naming (current)
Preferred constructors are neutral and explicit:
- `ClientConfig::apiToken(...)`
- `ClientConfig::oauth2(...)`

### Deprecated aliases (still available)
- `ClientConfig::productionApiToken()`
- `ClientConfig::sandboxApiToken()`
- `ClientConfig::productionOAuth2()`
- `ClientConfig::sandboxOAuth2()`

### Before -> After

```php
// Before
$cfg = ClientConfig::productionApiToken($token);
$cfg = ClientConfig::sandboxApiToken($token);
$cfg = ClientConfig::productionOAuth2($accessToken);
$cfg = ClientConfig::sandboxOAuth2($accessToken);

// After
$cfg = ClientConfig::apiToken($token);
$cfg = ClientConfig::apiToken($token, ClientConfig::SANDBOX_BASE_URL);
$cfg = ClientConfig::oauth2($accessToken);
$cfg = ClientConfig::oauth2($accessToken, ClientConfig::SANDBOX_BASE_URL);
```

## Profile endpoint version alignment
Profile operations use current v2 endpoints:
- `GET /v2/profiles`
- `GET /v2/profiles/{profileId}`

If you previously relied on v1 profile path assumptions, update assertions and integration mocks accordingly.

## Request validation behavior
Recent versions add fail-fast request validation (throws `ValidationException`) for invalid input, such as:
- invalid currency code format
- zero/negative amounts
- missing required fields
- mutually exclusive field conflicts

### Suggested migration approach
1. Add tests for request object construction in your app.
2. Catch `ValidationException` where requests are composed from user input.
3. Normalize inputs (currency uppercase, trimmed strings) before SDK request construction.
