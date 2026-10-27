# Upgrade Guide

## sudiptpa/wise-php-sdk 0.1 to theocode12/wise-php-sdk 0.2
This package continues `sudiptpa/wise-php-sdk` under a new name and namespace.

1. Swap the dependency:
   ```bash
   composer remove sudiptpa/wise-php-sdk
   composer require theocode12/wise-php-sdk:^0.2
   ```
2. Replace the namespace prefix `Sujip\Wise\` with `Theocode\Wise\` in your code (`use` statements, type hints and
   any string class names).
3. If you relied on the default User-Agent, it is now `theocode12/wise-php-sdk`.
4. Review the deprecations in [CHANGELOG.md](CHANGELOG.md): `WebhookVerifier` (use `WebhookSignatureVerifier`),
   the `CreateWebhookSubscriptionRequest` constructor (use `::for()`), and `CreateUserTokenRequest::registrationCode()`
   (use `registrationCodeForEmail()`).

## 0.x to 0.1
- Initial release; no upgrade actions required.
