# Wise API Status Matrix

Reference sources:
- Wise API reference: https://docs.wise.com/api-reference
- Deprecated APIs: https://docs.wise.com/api-reference/deprecated

Status legend:
- `Implemented`: available in this SDK today.
- `Planned`: current (non-deprecated) Wise API resource, not implemented yet.
- `Skipped (Deprecated)`: listed under Wise deprecated APIs and intentionally excluded.

## Implemented Endpoint Coverage

| Resource | Endpoint | Status | Notes |
|---|---|---|---|
| Profile | `GET /v2/profiles` | Implemented | list |
| Profile | `GET /v2/profiles/{profileId}` | Implemented | get |
| Quote | `POST /v3/quotes` | Implemented | create unauthenticated |
| Quote | `POST /v3/profiles/{profileId}/quotes` | Implemented | create authenticated |
| Quote | `GET /v3/profiles/{profileId}/quotes/{quoteId}` | Implemented | get |
| Quote | `PATCH /v3/profiles/{profileId}/quotes/{quoteId}` | Implemented | update |
| Recipient Account | `POST /v1/accounts` | Implemented | create |
| Recipient Account | `GET /v1/accounts` | Implemented | list |
| Recipient Account | `GET /v1/accounts/{accountId}` | Implemented | get |
| Transfer | `POST /v1/transfers` | Implemented | create |
| Transfer | `GET /v1/transfers/{transferId}` | Implemented | get |
| Transfer | `POST /v1/transfer-requirements` | Implemented | requirements |
| Payment | `POST /v3/profiles/{profileId}/transfers/{transferId}/payments` | Implemented | fund transfer |
| Webhook | `POST /v3/applications/{clientKey}/subscriptions` | Implemented | create app subscription |
| Webhook | `GET /v3/applications/{clientKey}/subscriptions` | Implemented | list app subscriptions |
| Webhook | `GET /v3/applications/{clientKey}/subscriptions/{subscriptionId}` | Implemented | get app subscription |
| Webhook | `DELETE /v3/applications/{clientKey}/subscriptions/{subscriptionId}` | Implemented | delete app subscription |
| Webhook | `POST /v3/applications/{clientKey}/subscriptions/{subscriptionId}/test-notifications` | Implemented | app test notification |
| Webhook | `POST /v3/profiles/{profileId}/subscriptions` | Implemented | create profile subscription |
| Webhook | `GET /v3/profiles/{profileId}/subscriptions` | Implemented | list profile subscriptions |
| Webhook | `GET /v3/profiles/{profileId}/subscriptions/{subscriptionId}` | Implemented | get profile subscription |
| Webhook | `DELETE /v3/profiles/{profileId}/subscriptions/{subscriptionId}` | Implemented | delete profile subscription |
| Activity | `GET /v1/profiles/{profileId}/activities` | Implemented | list and iterate |
| Contact | `POST /v2/profiles/{profileId}/contacts` | Implemented | direct identifier creation |
| Currencies | `GET /v1/currencies` | Implemented | list supported currencies |
| Address | `POST /v1/addresses` | Implemented | create address |
| Address | `GET /v1/addresses` | Implemented | list addresses |
| Address | `GET /v1/addresses/{addressId}` | Implemented | get address |
| Address | `GET /v1/address-requirements` | Implemented | generic requirements |
| Address | `POST /v1/address-requirements` | Implemented | resolve requirements by payload |
| Balance | `POST /v4/profiles/{profileId}/balances` | Implemented | create |
| Balance | `GET /v4/profiles/{profileId}/balances` | Implemented | list |
| Balance | `GET /v4/profiles/{profileId}/balances/{balanceId}` | Implemented | get |
| Balance | `DELETE /v4/profiles/{profileId}/balances/{balanceId}` | Implemented | close |
| Balance | `POST /v2/profiles/{profileId}/balance-movements` | Implemented | move between balances |
| Balance | `GET /v1/profiles/{profileId}/balance-capacity` | Implemented | capacity |
| Balance | `POST /v1/profiles/{profileId}/excess-money-account` | Implemented | add excess money account |
| Balance | `GET /v1/profiles/{profileId}/total-funds/{currency}` | Implemented | total funds |
| Rate | `GET /v1/rates` | Implemented | list rates with optional filters |

## Current Wise API Resources Not Yet Implemented

| Resource | Status | Reason |
|---|---|---|
| 3D Secure Authentication | Planned | not in current payment-core SDK scope |
| Additional Customer Verification | Planned | not in current payment-core SDK scope |
| Balance Statement | Planned | not in current payment-core SDK scope |
| Bank Account Details | Planned | not in current payment-core SDK scope |
| Batch Group | Planned | not in current payment-core SDK scope |
| Bulk Settlement | Planned | not in current payment-core SDK scope |
| Card | Planned | not in current payment-core SDK scope |
| Card Kiosk Collection | Planned | not in current payment-core SDK scope |
| Card Order | Planned | not in current payment-core SDK scope |
| Card Transaction | Planned | not in current payment-core SDK scope |
| Claim Account | Planned | not in current payment-core SDK scope |
| Client Credentials Token | Planned | not in current payment-core SDK scope |
| Comparison | Planned | not in current payment-core SDK scope |
| Delivery Estimate | Planned | not in current payment-core SDK scope |
| Direct Debit Account | Planned | not in current payment-core SDK scope |
| Disputes | Planned | not in current payment-core SDK scope |
| FaceTec | Planned | not in current payment-core SDK scope |
| JOSE | Planned | not in current payment-core SDK scope |
| KYC Review | Planned | not in current payment-core SDK scope |
| Multi Currency Account | Planned | not in current payment-core SDK scope |
| One Time Token | Planned | not in current payment-core SDK scope |
| Partner Cases | Planned | not in current payment-core SDK scope |
| Payin Deposit Detail | Planned | not in current payment-core SDK scope |
| Push Provisioning | Planned | not in current payment-core SDK scope |
| Simulation | Planned | not in current payment-core SDK scope |
| Spend Controls | Planned | not in current payment-core SDK scope |
| Spend Limits | Planned | not in current payment-core SDK scope |
| Strong Customer Authentication | Planned | not in current payment-core SDK scope |
| User | Planned | not in current payment-core SDK scope |
| User Security | Planned | not in current payment-core SDK scope |
| User Tokens | Planned | not in current payment-core SDK scope |

## Deprecated API Groups (Intentionally Skipped)

| Deprecated Group | Status | Reason |
|---|---|---|
| Account (deprecated group) | Skipped (Deprecated) | excluded by SDK policy |
| Payment (deprecated group) | Skipped (Deprecated) | excluded by SDK policy |
| Quote (deprecated group) | Skipped (Deprecated) | excluded by SDK policy |
| Recipients (deprecated group) | Skipped (Deprecated) | excluded by SDK policy |
