<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Currencies\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Currency implements Hydratable
{
    /**
     * @param  list<string>  $countryKeywords
     */
    public function __construct(
        public string $code,
        public string $name,
        public ?string $symbol,
        public array $countryKeywords,
        public bool $supportsDecimals,
    ) {}

    public static function fromArray(array $data): static
    {
        $keywords = [];
        foreach (($data['countryKeywords'] ?? []) as $keyword) {
            if (is_string($keyword) && $keyword !== '') {
                $keywords[] = $keyword;
            }
        }

        return new self(
            code: strtoupper(Cast::string($data, 'code', '') ?? ''),
            name: Cast::string($data, 'name', '') ?? '',
            symbol: Cast::string($data, 'symbol'),
            countryKeywords: $keywords,
            supportsDecimals: Cast::bool($data, 'supportsDecimals', true) ?? true,
        );
    }
}
