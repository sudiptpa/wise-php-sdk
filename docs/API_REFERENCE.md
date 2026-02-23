# API Reference

Implemented operations in this SDK (non-deprecated scope only).

## Quote

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `quote()->createUnauthenticated()` | `POST` | `/v3/quotes` | none | `CreateUnauthenticatedQuoteRequest` | `Quote` |
| `quote()->createAuthenticated()` | `POST` | `/v3/profiles/{profileId}/quotes` | token/oauth2 | `CreateAuthenticatedQuoteRequest` | `Quote` |
| `quote()->get()` | `GET` | `/v3/profiles/{profileId}/quotes/{quoteId}` | token/oauth2 | - | `Quote` |
| `quote()->update()` | `PATCH` | `/v3/profiles/{profileId}/quotes/{quoteId}` | token/oauth2 | `UpdateQuoteRequest` | `Quote` |

## Recipient Account

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `recipientAccount()->create()` | `POST` | `/v1/accounts` | token/oauth2 | `CreateRecipientAccountRequest` | `RecipientAccount` |
| `recipientAccount()->list()` | `GET` | `/v1/accounts` | token/oauth2 | optional `profile` query | `RecipientAccountCollection` |
| `recipientAccount()->get()` | `GET` | `/v1/accounts/{accountId}` | token/oauth2 | - | `RecipientAccount` |

## Transfer

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `transfer()->create()` | `POST` | `/v1/transfers` | token/oauth2 | `CreateTransferRequest` | `Transfer` |
| `transfer()->get()` | `GET` | `/v1/transfers/{transferId}` | token/oauth2 | - | `Transfer` |
| `transfer()->requirements()` | `POST` | `/v1/transfer-requirements` | token/oauth2 | `TransferRequirementsRequest` | `TransferRequirements` |

## Payment / Funding

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `payment()->fundTransfer()` | `POST` | `/v3/profiles/{profileId}/transfers/{transferId}/payments` | token/oauth2 | `FundTransferRequest` | `Payment` |

## Webhook

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `webhook()->createApplicationSubscription()` | `POST` | `/v3/applications/{clientKey}/subscriptions` | token/oauth2 | `CreateWebhookSubscriptionRequest` | `WebhookSubscription` |
| `webhook()->listApplicationSubscriptions()` | `GET` | `/v3/applications/{clientKey}/subscriptions` | token/oauth2 | - | `WebhookSubscriptionCollection` |
| `webhook()->getApplicationSubscription()` | `GET` | `/v3/applications/{clientKey}/subscriptions/{subscriptionId}` | token/oauth2 | - | `WebhookSubscription` |
| `webhook()->deleteApplicationSubscription()` | `DELETE` | `/v3/applications/{clientKey}/subscriptions/{subscriptionId}` | token/oauth2 | - | `void` |
| `webhook()->sendApplicationTestNotification()` | `POST` | `/v3/applications/{clientKey}/subscriptions/{subscriptionId}/test-notifications` | token/oauth2 | - | `void` |
| `webhook()->createProfileSubscription()` | `POST` | `/v3/profiles/{profileId}/subscriptions` | token/oauth2 | `CreateWebhookSubscriptionRequest` | `WebhookSubscription` |
| `webhook()->listProfileSubscriptions()` | `GET` | `/v3/profiles/{profileId}/subscriptions` | token/oauth2 | - | `WebhookSubscriptionCollection` |
| `webhook()->getProfileSubscription()` | `GET` | `/v3/profiles/{profileId}/subscriptions/{subscriptionId}` | token/oauth2 | - | `WebhookSubscription` |
| `webhook()->deleteProfileSubscription()` | `DELETE` | `/v3/profiles/{profileId}/subscriptions/{subscriptionId}` | token/oauth2 | - | `void` |

## Activity

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `activity()->list()` | `GET` | `/v1/profiles/{profileId}/activities` | token/oauth2 | `ListActivitiesRequest` | `ActivityPage` |
| `activity()->iterate()` | repeated `GET` | `/v1/profiles/{profileId}/activities` | token/oauth2 | `ListActivitiesRequest` | `Generator<Activity>` |

Supported filters in `ListActivitiesRequest`:
- `monetaryResourceType`
- `status`
- `since`
- `until`
- `nextCursor`
- `size`

## Profile

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `profile()->list()` | `GET` | `/v2/profiles` | token/oauth2 | - | `Collection<Profile>` |
| `profile()->get()` | `GET` | `/v2/profiles/{profileId}` | token/oauth2 | - | `Profile` |
