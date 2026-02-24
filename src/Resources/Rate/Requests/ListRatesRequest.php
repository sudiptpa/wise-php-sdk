<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Rate\Requests;

use DateTimeInterface;
use Sujip\Wise\Exceptions\ValidationException;

final readonly class ListRatesRequest
{
    public function __construct(
        public ?string $source = null,
        public ?string $target = null,
        public ?DateTimeInterface $time = null,
        public ?DateTimeInterface $from = null,
        public ?DateTimeInterface $to = null,
        public ?string $group = null,
    ) {
        if (($this->source === null) !== ($this->target === null)) {
            throw new ValidationException('source and target must be provided together.');
        }

        if ($this->source !== null && preg_match('/^[A-Z]{3}$/i', $this->source) !== 1) {
            throw new ValidationException('source must be a valid 3-letter currency code.');
        }

        if ($this->target !== null && preg_match('/^[A-Z]{3}$/i', $this->target) !== 1) {
            throw new ValidationException('target must be a valid 3-letter currency code.');
        }

        if ($this->group !== null) {
            $group = strtolower(trim($this->group));
            if (! in_array($group, ['day', 'hour', 'minute'], true)) {
                throw new ValidationException('group must be one of: day, hour, minute.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return array_filter([
            'source' => $this->source === null ? null : strtoupper($this->source),
            'target' => $this->target === null ? null : strtoupper($this->target),
            'time' => $this->time?->format(DATE_ATOM),
            'from' => $this->from?->format(DATE_ATOM),
            'to' => $this->to?->format(DATE_ATOM),
            'group' => $this->group === null ? null : strtolower($this->group),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
