<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\BankAccountDetails;

use Sujip\Wise\Resources\BankAccountDetails\Collections\BankAccountDetailsCollection;
use Sujip\Wise\Resources\BankAccountDetails\Collections\BankAccountDetailsOrderCollection;
use Sujip\Wise\Resources\BankAccountDetails\Models\BankAccountDetail;
use Sujip\Wise\Resources\BankAccountDetails\Models\BankAccountDetailsOrder;
use Sujip\Wise\Resources\BankAccountDetails\Models\PaymentReturn;
use Sujip\Wise\Resources\BankAccountDetails\Requests\CreateBankDetailsRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\CreateDetailsOrderRequest;
use Sujip\Wise\Resources\BankAccountDetails\Requests\MarkPaymentReturnRequest;
use Sujip\Wise\Resources\Resource;

final class BankAccountDetailsResource extends Resource
{
    public function createOrder(int $profileId, CreateDetailsOrderRequest $request): BankAccountDetailsOrder
    {
        $payload = $this->client->request('POST', "/v1/profiles/{$profileId}/account-details-orders", body: $request->toArray());

        return BankAccountDetailsOrder::fromArray($payload);
    }

    public function createBankDetails(int $profileId, CreateBankDetailsRequest $request): BankAccountDetail
    {
        $payload = $this->client->request('POST', "/v3/profiles/{$profileId}/bank-details", body: $request->toArray());

        return BankAccountDetail::fromArray($payload);
    }

    public function list(int $profileId): BankAccountDetailsCollection
    {
        $payload = $this->client->request('GET', "/v1/profiles/{$profileId}/account-details");
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = BankAccountDetail::fromArray($item);
            }
        }

        return new BankAccountDetailsCollection($items);
    }

    public function listOrders(int $profileId): BankAccountDetailsOrderCollection
    {
        $payload = $this->client->request('GET', "/v3/profiles/{$profileId}/account-details-orders");
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = BankAccountDetailsOrder::fromArray($item);
            }
        }

        return new BankAccountDetailsOrderCollection($items);
    }

    public function markPaymentReturn(int $profileId, int $paymentId, MarkPaymentReturnRequest $request): PaymentReturn
    {
        $payload = $this->client->request(
            'POST',
            "/v1/profiles/{$profileId}/account-details/payments/{$paymentId}/returns",
            body: $request->toArray(),
        );

        return PaymentReturn::fromArray($payload);
    }
}
