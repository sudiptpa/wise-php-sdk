<?php

declare(strict_types=1);

use Theocode\Wise\Config\ClientConfig;
use Theocode\Wise\Resources\Payment\Requests\FundTransferRequest;
use Theocode\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Theocode\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Theocode\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Theocode\Wise\Transport\Psr18Transport;
use Theocode\Wise\Wise;

require __DIR__.'/../vendor/autoload.php';

$config = ClientConfig::apiToken('your-token');
$transport = new Psr18Transport($yourPsr18Client);
$wise = Wise::client($config, $transport, $yourRequestFactory, $yourStreamFactory);

$quote = $wise->quote()->createAuthenticated(123, CreateAuthenticatedQuoteRequest::fixedTarget('USD', 'EUR', 100));
$recipient = $wise->recipientAccount()->create(new CreateRecipientAccountRequest(123, 'Jane Doe', 'EUR', 'iban', ['iban' => 'DE123']));
$transfer = $wise->transfer()->create(CreateTransferRequest::from($quote, $recipient));
$payment = $wise->payment()->fundTransfer(123, $transfer->id, new FundTransferRequest('BALANCE'));

var_dump($payment->transferId);
