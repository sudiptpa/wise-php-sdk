# Endpoint Coverage Matrix

This matrix is the release gate source for endpoint verification.

Rules:
- Only non-deprecated endpoints are implemented.
- Every implemented endpoint must have unit-test evidence.
- Every implemented endpoint must be exercised by the full sandbox check workflow.

## Quote

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `quote()->createUnauthenticated()` | `POST /v3/quotes` | `QuoteResourceTest::test_creates_unauthenticated_quote_without_authorization_header` | `sandbox_full_check.php` |
| `quote()->createAuthenticated()` | `POST /v3/profiles/{profileId}/quotes` | `QuoteResourceTest::test_creates_authenticated_quote_and_builds_request`, `EndpointContractTest::test_quote_create_authenticated_contract_body_contains_expected_keys` | `sandbox_full_check.php` |
| `quote()->get()` | `GET /v3/profiles/{profileId}/quotes/{quoteId}` | `EndpointContractTest::test_quote_get_and_patch_contract` | `sandbox_full_check.php` |
| `quote()->update()` | `PATCH /v3/profiles/{profileId}/quotes/{quoteId}` | `QuoteResourceTest::test_updates_quote`, `EndpointContractTest::test_quote_get_and_patch_contract` | `sandbox_full_check.php` |

## Recipient Account

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `recipientAccount()->create()` | `POST /v1/accounts` | `RecipientAccountResourceTest::test_creates_recipient_account`, `EndpointContractTest::test_recipient_create_contract_body_contains_expected_keys` | `sandbox_full_check.php` |
| `recipientAccount()->list()` | `GET /v1/accounts` | `EndpointContractTest::test_recipient_account_list_and_get_contract` | `sandbox_full_check.php` |
| `recipientAccount()->get()` | `GET /v1/accounts/{accountId}` | `EndpointContractTest::test_recipient_account_list_and_get_contract` | `sandbox_full_check.php` |

## Transfer

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `transfer()->create()` | `POST /v1/transfers` | `TransferResourceTest::test_creates_transfer_and_helper_states` | `sandbox_full_check.php` |
| `transfer()->get()` | `GET /v1/transfers/{transferId}` | `EndpointContractTest::test_transfer_get_and_requirements_contract` | `sandbox_full_check.php` |
| `transfer()->requirements()` | `POST /v1/transfer-requirements` | `TransferResourceTest::test_fetches_transfer_requirements`, `EndpointContractTest::test_transfer_get_and_requirements_contract` | `sandbox_full_check.php` |

## Payment

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `payment()->fundTransfer()` | `POST /v3/profiles/{profileId}/transfers/{transferId}/payments` | `PaymentResourceTest::test_funds_transfer`, `EndpointContractTest::test_payment_fund_transfer_contract` | `sandbox_full_check.php` |

## Webhook

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `webhook()->createApplicationSubscription()` | `POST /v3/applications/{clientKey}/subscriptions` | `WebhookResourceTest::test_creates_application_subscription`, `EndpointContractTest::test_webhook_application_contracts` | `sandbox_full_check.php` |
| `webhook()->listApplicationSubscriptions()` | `GET /v3/applications/{clientKey}/subscriptions` | `EndpointContractTest::test_webhook_application_contracts` | `sandbox_full_check.php` |
| `webhook()->getApplicationSubscription()` | `GET /v3/applications/{clientKey}/subscriptions/{subscriptionId}` | `EndpointContractTest::test_webhook_application_contracts` | `sandbox_full_check.php` |
| `webhook()->deleteApplicationSubscription()` | `DELETE /v3/applications/{clientKey}/subscriptions/{subscriptionId}` | `EndpointContractTest::test_webhook_application_contracts` | `sandbox_full_check.php` |
| `webhook()->sendApplicationTestNotification()` | `POST /v3/applications/{clientKey}/subscriptions/{subscriptionId}/test-notifications` | `WebhookResourceTest::test_sends_application_test_notification`, `EndpointContractTest::test_webhook_application_contracts` | `sandbox_full_check.php` |
| `webhook()->createProfileSubscription()` | `POST /v3/profiles/{profileId}/subscriptions` | `EndpointContractTest::test_webhook_profile_contracts` | `sandbox_full_check.php` |
| `webhook()->listProfileSubscriptions()` | `GET /v3/profiles/{profileId}/subscriptions` | `WebhookResourceTest::test_lists_and_gets_profile_subscriptions`, `EndpointContractTest::test_webhook_profile_contracts` | `sandbox_full_check.php` |
| `webhook()->getProfileSubscription()` | `GET /v3/profiles/{profileId}/subscriptions/{subscriptionId}` | `WebhookResourceTest::test_lists_and_gets_profile_subscriptions`, `EndpointContractTest::test_webhook_profile_contracts` | `sandbox_full_check.php` |
| `webhook()->deleteProfileSubscription()` | `DELETE /v3/profiles/{profileId}/subscriptions/{subscriptionId}` | `EndpointContractTest::test_webhook_profile_contracts` | `sandbox_full_check.php` |

## Activity

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `activity()->list()` | `GET /v1/profiles/{profileId}/activities` | `ActivityResourceTest::test_lists_activities_with_cursor_pagination` | `sandbox_full_check.php` |
| `activity()->iterate()` | repeated `GET /v1/profiles/{profileId}/activities` | `ActivityResourceTest::test_iterates_across_pages` | `sandbox_full_check.php` |

## Profile

| Method | Path | Unit coverage | E2E coverage |
|---|---|---|---|
| `profile()->list()` | `GET /v2/profiles` | `ProfileResourceTest::test_lists_profiles` | `sandbox_full_check.php` |
| `profile()->get()` | `GET /v2/profiles/{profileId}` | `ProfileResourceTest::test_gets_profile_by_id`, `EndpointContractTest::test_profile_get_contract` | `sandbox_full_check.php` |
