<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Resources\Address\Requests\CreateAddressRequest;
use Sujip\Wise\Resources\Address\Requests\ResolveAddressRequirementsRequest;
use Sujip\Wise\Resources\Balance\Requests\AddExcessMoneyAccountRequest;
use Sujip\Wise\Resources\Balance\Requests\CreateBalanceMovementRequest;
use Sujip\Wise\Resources\Balance\Requests\CreateBalanceRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\CreateBankDetailsRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\CreateDetailsOrderRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\MarkPaymentReturnRequest;
use Sujip\Wise\Resources\Contact\Requests\CreateContactRequest;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateUnauthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Resources\Rate\Requests\ListRatesRequest;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Sujip\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
use Sujip\Wise\Resources\User\Requests\CreateRegistrationCodeRequest;
use Sujip\Wise\Resources\User\Requests\UpdateUserContactEmailRequest;
use Sujip\Wise\Resources\User\Requests\UserExistsRequest;
use Sujip\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;
use Sujip\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;

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
