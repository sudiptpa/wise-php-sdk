<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Hydration;

use PHPUnit\Framework\TestCase;
use Theocode\Wise\Hydration\Hydrator;
use Theocode\Wise\Resources\Quote\Models\Quote;

final class HydratorTest extends TestCase
{
    public function test_hydrates_model_from_array(): void
    {
        $hydrator = new Hydrator;
        $data = [
            'id' => 1,
            'sourceCurrency' => 'USD',
            'targetCurrency' => 'EUR',
            'sourceAmount' => 50,
            'targetAmount' => 46,
            'fee' => ['total' => 1],
            'rate' => 0.92,
        ];

        $quote = $hydrator->hydrate(Quote::class, $data);

        self::assertInstanceOf(Quote::class, $quote);
        self::assertSame('1', $quote->id);
    }
}
