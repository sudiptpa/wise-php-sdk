<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateUnauthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Sujip\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
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
}
