<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Resources\Address\Requests\CreateAddressRequest;
use Theocode\Wise\Resources\Address\Requests\ResolveAddressRequirementsRequest;
use Theocode\Wise\Resources\Balance\Requests\AddExcessMoneyAccountRequest;
use Theocode\Wise\Resources\Balance\Requests\CreateBalanceMovementRequest;
use Theocode\Wise\Resources\Balance\Requests\CreateBalanceRequest;
use Theocode\Wise\Resources\BankAccountDetails\Requests\CreateBankDetailsRequest;
use Theocode\Wise\Resources\BankAccountDetails\Requests\CreateDetailsOrderRequest;
use Theocode\Wise\Resources\BankAccountDetails\Requests\MarkPaymentReturnRequest;
use Theocode\Wise\Resources\Contact\Requests\CreateContactRequest;
use Theocode\Wise\Resources\Payment\Requests\FundTransferRequest;
use Theocode\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Theocode\Wise\Resources\Quote\Requests\CreateUnauthenticatedQuoteRequest;
use Theocode\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Theocode\Wise\Resources\Rate\Requests\ListRatesRequest;
use Theocode\Wise\Resources\RecipientAccount\Requests\AccountRequirementsOptions;
use Theocode\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Theocode\Wise\Resources\Transfer\Enums\TransferStatus;
use Theocode\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Theocode\Wise\Resources\Transfer\Requests\ListTransfersRequest;
use Theocode\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
use Theocode\Wise\Resources\User\Requests\CreateRegistrationCodeRequest;
use Theocode\Wise\Resources\User\Requests\UpdateUserContactEmailRequest;
use Theocode\Wise\Resources\User\Requests\UserExistsRequest;
use Theocode\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;
use Theocode\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;

final class RequestValidationTest extends TestCase
{
    public function test_create_authenticated_quote_requires_exactly_one_amount(): void
    {
        $this->expectException(ValidationException::class);
        new CreateAuthenticatedQuoteRequest('USD', 'EUR', null, null);
    }

    public function test_create_authenticated_quote_rejects_invalid_currency(): void
    {
        $this->expectException(ValidationException::class);
        new CreateAuthenticatedQuoteRequest('US', 'EUR', 10.0, null);
    }

    public function test_create_unauthenticated_quote_rejects_non_positive_amount(): void
    {
        $this->expectException(ValidationException::class);
        new CreateUnauthenticatedQuoteRequest('USD', 'EUR', 0.0);
    }

    public function test_update_quote_requires_at_least_one_field(): void
    {
        $this->expectException(ValidationException::class);
        new UpdateQuoteRequest;
    }

    public function test_update_quote_rejects_empty_preferred_pay_in(): void
    {
        $this->expectException(ValidationException::class);
        new UpdateQuoteRequest(preferredPayIn: '   ');
    }

    public function test_recipient_account_request_requires_profile_and_details(): void
    {
        $this->expectException(ValidationException::class);
        new CreateRecipientAccountRequest(0, 'Jane', 'EUR', 'iban', []);
    }

    public function test_account_requirements_options_rejects_invalid_originator_legal_entity_type(): void
    {
        $this->expectException(ValidationException::class);
        new AccountRequirementsOptions(originatorLegalEntityType: 'SOLE_TRADER');
    }

    public function test_create_transfer_request_rejects_invalid_identifiers(): void
    {
        $this->expectException(ValidationException::class);
        new CreateTransferRequest(0, '', null);
    }

    public function test_transfer_requirements_request_payload_cannot_be_empty(): void
    {
        $this->expectException(ValidationException::class);
        new TransferRequirementsRequest([]);
    }

    public function test_list_transfers_request_rejects_non_positive_profile(): void
    {
        $this->expectException(ValidationException::class);
        new ListTransfersRequest(profile: 0);
    }

    public function test_list_transfers_request_rejects_invalid_limit(): void
    {
        $this->expectException(ValidationException::class);
        new ListTransfersRequest(limit: 0);
    }

    public function test_list_transfers_request_rejects_negative_offset(): void
    {
        $this->expectException(ValidationException::class);
        new ListTransfersRequest(offset: -1);
    }

    public function test_list_transfers_request_serializes_statuses_and_dates(): void
    {
        $request = new ListTransfersRequest(
            statuses: [TransferStatus::Processing, 'cancelled'],
            createdDateStart: new DateTimeImmutable('2024-03-01'),
            createdDateEnd: new DateTimeImmutable('2024-03-31'),
        );

        self::assertSame([
            'status' => 'processing,cancelled',
            'createdDateStart' => '2024-03-01',
            'createdDateEnd' => '2024-03-31',
        ], $request->toQuery());
    }

    public function test_fund_transfer_request_rejects_unsupported_type(): void
    {
        $this->expectException(ValidationException::class);
        new FundTransferRequest('CARD');
    }

    public function test_webhook_subscription_request_requires_valid_url(): void
    {
        $this->expectException(ValidationException::class);
        new CreateWebhookSubscriptionRequest('not-a-url', 'name');
    }

    public function test_webhook_subscription_documented_body_rejects_an_empty_trigger(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('trigger_on cannot be empty.');

        CreateWebhookSubscriptionRequest::for(
            name: 'Transfers Hook',
            triggerOn: '  ',
            url: 'https://example.com/hook',
        );
    }

    public function test_balance_requests_reject_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new CreateBalanceRequest([]);
    }

    public function test_balance_movement_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new CreateBalanceMovementRequest([]);
    }

    public function test_excess_money_account_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new AddExcessMoneyAccountRequest([]);
    }

    public function test_list_rates_request_requires_source_and_target_together(): void
    {
        $this->expectException(ValidationException::class);
        new ListRatesRequest(source: 'USD');
    }

    public function test_list_rates_request_rejects_invalid_group(): void
    {
        $this->expectException(ValidationException::class);
        new ListRatesRequest(source: 'USD', target: 'EUR', group: 'week');
    }

    public function test_contact_request_validates_currency_and_identifier(): void
    {
        $this->expectException(ValidationException::class);
        new CreateContactRequest('US', ' ');
    }

    public function test_create_address_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new CreateAddressRequest([]);
    }

    public function test_address_requirements_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new ResolveAddressRequirementsRequest([]);
    }

    public function test_create_details_order_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new CreateDetailsOrderRequest([]);
    }

    public function test_create_bank_details_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new CreateBankDetailsRequest([]);
    }

    public function test_mark_payment_return_request_rejects_empty_payload(): void
    {
        $this->expectException(ValidationException::class);
        new MarkPaymentReturnRequest([]);
    }

    public function test_user_exists_request_rejects_invalid_email(): void
    {
        $this->expectException(ValidationException::class);
        new UserExistsRequest('not-an-email');
    }

    public function test_create_registration_code_request_rejects_invalid_email(): void
    {
        $this->expectException(ValidationException::class);
        new CreateRegistrationCodeRequest('not-an-email');
    }

    public function test_update_user_contact_email_request_rejects_invalid_email(): void
    {
        $this->expectException(ValidationException::class);
        new UpdateUserContactEmailRequest('not-an-email');
    }

    public function test_user_token_request_builders_produce_payload(): void
    {
        $payload = CreateUserTokenRequest::refreshToken('client', 'secret', 'refresh')->toForm();

        self::assertSame('refresh_token', $payload['grant_type'] ?? null);
        self::assertSame('refresh', $payload['refresh_token'] ?? null);
        self::assertArrayNotHasKey('client_secret', $payload);
    }

    public function test_user_token_request_rejects_empty_client_id(): void
    {
        $this->expectException(ValidationException::class);
        CreateUserTokenRequest::refreshToken('', 'secret', 'refresh');
    }

    public function test_user_token_request_rejects_invalid_redirect_uri(): void
    {
        $this->expectException(ValidationException::class);
        CreateUserTokenRequest::authorizationCode('client', 'secret', 'code', 'not-a-url');
    }
}
