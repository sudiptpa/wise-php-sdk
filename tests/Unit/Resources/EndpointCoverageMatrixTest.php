<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class EndpointCoverageMatrixTest extends TestCase
{
    /**
     * @var array<string, list<string>>
     */
    private const TEST_EVIDENCE = [
        'Sujip\\Wise\\Resources\\Activity\\ActivityResource::iterate' => [
            'tests/Unit/Resources/Activity/ActivityResourceTest.php::test_iterates_across_pages',
        ],
        'Sujip\\Wise\\Resources\\Activity\\ActivityResource::list' => [
            'tests/Unit/Resources/Activity/ActivityResourceTest.php::test_lists_activities_with_cursor_pagination',
        ],
        'Sujip\\Wise\\Resources\\Payment\\PaymentResource::fundTransfer' => [
            'tests/Unit/Resources/Payment/PaymentResourceTest.php::test_funds_transfer',
            'tests/Unit/Resources/EndpointContractTest.php::test_payment_fund_transfer_contract',
        ],
        'Sujip\\Wise\\Resources\\Profile\\ProfileResource::get' => [
            'tests/Unit/Resources/Profile/ProfileResourceTest.php::test_gets_profile_by_id',
            'tests/Unit/Resources/EndpointContractTest.php::test_profile_get_contract',
        ],
        'Sujip\\Wise\\Resources\\Profile\\ProfileResource::list' => [
            'tests/Unit/Resources/Profile/ProfileResourceTest.php::test_lists_profiles',
        ],
        'Sujip\\Wise\\Resources\\Quote\\QuoteResource::createAuthenticated' => [
            'tests/Unit/Resources/Quote/QuoteResourceTest.php::test_creates_authenticated_quote_and_builds_request',
            'tests/Unit/Resources/EndpointContractTest.php::test_quote_create_authenticated_contract_body_contains_expected_keys',
        ],
        'Sujip\\Wise\\Resources\\Quote\\QuoteResource::createUnauthenticated' => [
            'tests/Unit/Resources/Quote/QuoteResourceTest.php::test_creates_unauthenticated_quote_without_authorization_header',
        ],
        'Sujip\\Wise\\Resources\\Quote\\QuoteResource::get' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_quote_get_and_patch_contract',
        ],
        'Sujip\\Wise\\Resources\\Quote\\QuoteResource::update' => [
            'tests/Unit/Resources/Quote/QuoteResourceTest.php::test_updates_quote',
            'tests/Unit/Resources/EndpointContractTest.php::test_quote_get_and_patch_contract',
        ],
        'Sujip\\Wise\\Resources\\RecipientAccount\\RecipientAccountResource::create' => [
            'tests/Unit/Resources/RecipientAccount/RecipientAccountResourceTest.php::test_creates_recipient_account',
            'tests/Unit/Resources/EndpointContractTest.php::test_recipient_create_contract_body_contains_expected_keys',
        ],
        'Sujip\\Wise\\Resources\\RecipientAccount\\RecipientAccountResource::get' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_recipient_account_list_and_get_contract',
        ],
        'Sujip\\Wise\\Resources\\RecipientAccount\\RecipientAccountResource::list' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_recipient_account_list_and_get_contract',
        ],
        'Sujip\\Wise\\Resources\\Transfer\\TransferResource::create' => [
            'tests/Unit/Resources/Transfer/TransferResourceTest.php::test_creates_transfer_and_helper_states',
        ],
        'Sujip\\Wise\\Resources\\Transfer\\TransferResource::get' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_transfer_get_and_requirements_contract',
        ],
        'Sujip\\Wise\\Resources\\Transfer\\TransferResource::requirements' => [
            'tests/Unit/Resources/Transfer/TransferResourceTest.php::test_fetches_transfer_requirements',
            'tests/Unit/Resources/EndpointContractTest.php::test_transfer_get_and_requirements_contract',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::createApplicationSubscription' => [
            'tests/Unit/Resources/Webhook/WebhookResourceTest.php::test_creates_application_subscription',
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_application_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::createProfileSubscription' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_profile_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::deleteApplicationSubscription' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_application_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::deleteProfileSubscription' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_profile_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::getApplicationSubscription' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_application_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::getProfileSubscription' => [
            'tests/Unit/Resources/Webhook/WebhookResourceTest.php::test_lists_and_gets_profile_subscriptions',
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_profile_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::listApplicationSubscriptions' => [
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_application_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::listProfileSubscriptions' => [
            'tests/Unit/Resources/Webhook/WebhookResourceTest.php::test_lists_and_gets_profile_subscriptions',
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_profile_contracts',
        ],
        'Sujip\\Wise\\Resources\\Webhook\\WebhookResource::sendApplicationTestNotification' => [
            'tests/Unit/Resources/Webhook/WebhookResourceTest.php::test_sends_application_test_notification',
            'tests/Unit/Resources/EndpointContractTest.php::test_webhook_application_contracts',
        ],
    ];

    public function test_every_implemented_endpoint_method_has_explicit_test_evidence(): void
    {
        $implemented = $this->implementedEndpointMethods();

        $actual = array_keys(self::TEST_EVIDENCE);
        sort($actual);

        self::assertSame(
            $implemented,
            $actual,
            'An endpoint method is implemented without a declared unit test evidence mapping.',
        );

        foreach (self::TEST_EVIDENCE as $endpointMethod => $evidenceList) {
            foreach ($evidenceList as $evidence) {
                [$relativePath, $testMethod] = explode('::', $evidence, 2);
                $absolutePath = __DIR__.'/../../../'.$relativePath;

                $code = file_get_contents($absolutePath);
                self::assertIsString($code, 'Missing test evidence file: '.$relativePath);
                self::assertStringContainsString(
                    'function '.$testMethod,
                    $code,
                    "Missing test method '{$testMethod}' in {$relativePath} for {$endpointMethod}.",
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function implementedEndpointMethods(): array
    {
        $resources = [
            'Sujip\\Wise\\Resources\\Activity\\ActivityResource',
            'Sujip\\Wise\\Resources\\Payment\\PaymentResource',
            'Sujip\\Wise\\Resources\\Profile\\ProfileResource',
            'Sujip\\Wise\\Resources\\Quote\\QuoteResource',
            'Sujip\\Wise\\Resources\\RecipientAccount\\RecipientAccountResource',
            'Sujip\\Wise\\Resources\\Transfer\\TransferResource',
            'Sujip\\Wise\\Resources\\Webhook\\WebhookResource',
        ];

        $methods = [];

        foreach ($resources as $resourceClass) {
            $reflection = new ReflectionClass($resourceClass);
            foreach ($reflection->getMethods() as $method) {
                if (! $method->isPublic() || $method->isStatic()) {
                    continue;
                }

                if ($method->getDeclaringClass()->getName() !== $resourceClass) {
                    continue;
                }

                $methods[] = $resourceClass.'::'.$method->getName();
            }
        }

        sort($methods);

        return $methods;
    }
}
