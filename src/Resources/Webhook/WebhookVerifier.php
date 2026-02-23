<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook;

final class WebhookVerifier
{
    public function verify(string $payload, string $providedSignature, string $secret): bool
    {
        $computed = base64_encode(hash_hmac('sha256', $payload, $secret, true));
        $providedSignature = $this->normalizeSignature($providedSignature);

        return hash_equals($computed, $providedSignature);
    }

    private function normalizeSignature(string $signature): string
    {
        $signature = trim($signature);
        if ($signature === '') {
            return '';
        }

        if (str_contains($signature, '=')) {
            $parts = explode('=', $signature, 2);
            $candidate = trim($parts[1] ?? '');
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return $signature;
    }
}
