<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook;

/**
 * @deprecated Wise signs webhook events with an RSA private key and publishes the matching public
 *             keys; it does not use a shared HMAC secret. Use {@see WebhookSignatureVerifier} instead.
 *             This class is kept unchanged for backwards compatibility.
 */
final class WebhookVerifier
{
    /**
     * @deprecated Use {@see WebhookSignatureVerifier::verify()} instead.
     */
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
