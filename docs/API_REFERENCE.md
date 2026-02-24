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

## Contact

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `contact()->create()` | `POST` | `/v2/profiles/{profileId}/contacts?isDirectIdentifierCreation=true` | token/oauth2 | `CreateContactRequest` | `Contact` |

## Currencies

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `currencies()->list()` | `GET` | `/v1/currencies` | token/oauth2 | optional locale (`Accept-Language`) | `CurrencyCollection` |

## Address

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `address()->create()` | `POST` | `/v1/addresses` | token/oauth2 | `CreateAddressRequest` | `Address` |
| `address()->list()` | `GET` | `/v1/addresses` | token/oauth2 | optional `profileId` query | `AddressCollection` |
| `address()->get()` | `GET` | `/v1/addresses/{addressId}` | token/oauth2 | - | `Address` |
| `address()->requirements()` | `GET` | `/v1/address-requirements` | token/oauth2 | - | `AddressRequirementCollection` |
| `address()->requirements($request)` | `POST` | `/v1/address-requirements` | token/oauth2 | `ResolveAddressRequirementsRequest` | `AddressRequirementCollection` |

## Balance

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `balance()->create()` | `POST` | `/v4/profiles/{profileId}/balances` | token/oauth2 | `CreateBalanceRequest` | `Balance` |
| `balance()->list()` | `GET` | `/v4/profiles/{profileId}/balances` | token/oauth2 | optional `types` query | `BalanceCollection` |
| `balance()->get()` | `GET` | `/v4/profiles/{profileId}/balances/{balanceId}` | token/oauth2 | - | `Balance` |
| `balance()->close()` | `DELETE` | `/v4/profiles/{profileId}/balances/{balanceId}` | token/oauth2 | - | `Balance` |
| `balance()->move()` | `POST` | `/v2/profiles/{profileId}/balance-movements` | token/oauth2 | `CreateBalanceMovementRequest` | `BalanceMovement` |
| `balance()->capacity()` | `GET` | `/v1/profiles/{profileId}/balance-capacity` | token/oauth2 | - | `BalanceCapacity` |
| `balance()->addExcessMoneyAccount()` | `POST` | `/v1/profiles/{profileId}/excess-money-account` | token/oauth2 | `AddExcessMoneyAccountRequest` | `ExcessMoneyAccount` |
| `balance()->totalFunds()` | `GET` | `/v1/profiles/{profileId}/total-funds/{currency}` | token/oauth2 | - | `TotalFunds` |

## Rate

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `rate()->list()` | `GET` | `/v1/rates` | token/oauth2 | optional `ListRatesRequest` query | `RateCollection` |

## Balance Statement

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `balanceStatement()->getJson()` | `GET` | `/v1/profiles/{profileId}/balance-statements/{balanceId}/statement.json` | token/oauth2 | optional `GetBalanceStatementRequest` query | `BalanceStatement` |

## Bank Account Details

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `bankAccountDetails()->createOrder()` | `POST` | `/v1/profiles/{profileId}/account-details-orders` | token/oauth2 | `CreateDetailsOrderRequest` | `BankAccountDetailsOrder` |
| `bankAccountDetails()->createBankDetails()` | `POST` | `/v3/profiles/{profileId}/bank-details` | token/oauth2 | `CreateBankDetailsRequest` | `BankAccountDetail` |
| `bankAccountDetails()->list()` | `GET` | `/v1/profiles/{profileId}/account-details` | token/oauth2 | - | `BankAccountDetailsCollection` |
| `bankAccountDetails()->listOrders()` | `GET` | `/v3/profiles/{profileId}/account-details-orders` | token/oauth2 | - | `BankAccountDetailsOrderCollection` |
| `bankAccountDetails()->markPaymentReturn()` | `POST` | `/v1/profiles/{profileId}/account-details/payments/{paymentId}/returns` | token/oauth2 | `MarkPaymentReturnRequest` | `PaymentReturn` |

## User

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `user()->me()` | `GET` | `/v1/me` | token/oauth2 | - | `User` |
| `user()->get()` | `GET` | `/v1/users/{userId}` | token/oauth2 | - | `User` |
| `user()->exists()` | `POST` | `/v1/users/exists` | token/oauth2 | `UserExistsRequest` | `UserExistsResult` |
| `user()->createRegistrationCode()` | `POST` | `/v1/user/signup/registration_code` | token/oauth2 | `CreateRegistrationCodeRequest` | `RegistrationCode` |
| `user()->updateContactEmail()` | `PUT` | `/v1/users/{userId}/contact-email` | token/oauth2 | `UpdateUserContactEmailRequest` | `UserContactEmail` |
| `user()->contactEmail()` | `GET` | `/v1/users/{userId}/contact-email` | token/oauth2 | - | `UserContactEmail` |

## User Tokens

| SDK Method | HTTP | Path | Auth | Request | Response |
|---|---|---|---|---|---|
| `userTokens()->create()` | `POST` | `/oauth/token` | none | `CreateUserTokenRequest` | `UserToken` |
