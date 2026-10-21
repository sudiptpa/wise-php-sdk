<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Webhook;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Resources\Webhook\WebhookSignatureVerifier;

final class WebhookSignatureVerifierTest extends TestCase
{
    /**
     * Test-only RSA keypairs. They are embedded so the suite does not depend on a local OpenSSL
     * configuration file, which `openssl_pkey_new()` needs before it can generate keys.
     */
    private const TEST_PRIVATE_KEY = <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCs+HZ9uRdWogz7
NrrmE64cLsm71F07Vy07Wv28FC1Wz7WKm8ncbS2slNRnFuV5D2/DfkxdmM+f4pok
EvsPR/ch96srVGUKo4ng4fEztx/TVK0I/MgWcwmfWZX14HJYFXWckp3siKdfJOep
mW1+Ufj3u2V8lDxvdamZny10FpWVKyUd8PCvF9jM5Vr6JOteO1aLXWHau4agd6PM
QMswi70zH4hfOlo0zioi1Z98VknFoPQU0mZQPE9bz+iVoJCbTiBMjh88JeoUczdL
r/AHAdqQqCTJFsrFVp99fC5Mhdkb8ySiDrhJDENbqZa0sduZC4QWDH0yU1Qeg1Z0
bWmktucHAgMBAAECggEATwo0vyuivgxMCUmPq05H7An3VlTrC5BBE5P3SYgg1cAR
kZax7f+X9PqHetosHx9OL5OZmtWW3tViIWTpA6kbhZKSzbiBeZSUj0Y//aVLLjtN
3MMCPbvwpDgAPjyDk7uupE8dSjaB4QjQsZ/yOFyF+9JOxFhKq5YlW5Uw5JDjZqdw
TLhpeqcjn9ZyDE2vBKvphKYlQib7ww23SwbeZ6bFpuhmgQGiXazzq4L3fbahDqPE
RQQFClMJB/b0Q9Y4hdVYeZP4TbZnfPRRRLeqta0bTdqZ7DyHK6mBvcpFJ+QGK9dB
ZJpmtxrVERscemWo5H64HtOQ+tRU5X9CfJTLKJL49QKBgQDtwJk1QY8Aoub+w2hA
BO4bH4KUPMhkB5Mfe+btwqdmWY+mTUR+5G0pu2JBvCT9VQAJjv29etji4Y5sdkwu
NtK1ajlaJplYaIymZ4XscRbQaewrW3OpZ6htRkEsdqCe527nCqHuSCJsSt9o2KGz
oglIvJMIP2AQk3ygm1JlgFsNrQKBgQC6PwUlN3p4603Y9PkV/opwx3uB8wDRQom4
88JB3gS1hDGugN1gHzO/jGu8oYh4xfwbFU2CsUEMSsu502QX0RXDSoxRU27EmWHn
MuT6hpjNDGbt5MvscAPYuswTkU1ovkKWc8R7uPdhDpMA4xE3yDxb9xLNUnmJ2q+5
GiAvk7h2AwKBgQDp9SwOrxu0yZk8PDhbPz677A8IJqQfYyDQy5FciL9IRXvJb6Gm
qMwPU/kFrMpNjJaD0XU6PygsYz9O1j5y/7nrkcMsRm2CBSfbHGY6XLLRlloz9ENm
Ab5JAehig73dgOquatUccVmGbUtZlkvRQ/OU4xRg+lRjG295cD7t6Rgw5QKBgQCM
emxPHgFqo1Eci+o1BME3P7+QzNGZBS8MKhAjzmqO3x76GIjhr6dAm5HHva9KKUt2
wiKsqTX/J4typleg/zwDCX+898lc0LRfxqufq1Ylk20vkpudwv+jwYGcYMSadKeV
A8z35RPySSPoT1z1Tjr4wuuTotm5eRGB9IrCWBGSMQKBgHOeDqm7CQ67jI86MvU7
vohSzpi2WG1ngCDKUHQsu0/EiuTFsjWzX3HYyEDZ882W+Jt2jD6c/sjV0/AQcYGw
MVYokIY3q3025apO0yXg47+qumFI5wXl3anck5aF57KHCJvjSj0svJFMNNmA0VcS
JrRFwwmYz6ZcSWYL7iGlPumz
-----END PRIVATE KEY-----
PEM;

    private const ROTATED_TEST_PRIVATE_KEY = <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCkC+62NRicQ3UB
DYwO2T8QOiRDqjn87d7OxMang9Ckk/146y1RuWkq3AydhvTZecLbTSTuG9A3ZCKo
p2DVmrQK4TExBJuTGHIh5YP5g0OVoMTR93CQIL+Eo7Z1asqj4waTapZZ8a9qP/gO
1wrAjbDywjdhSyPMS0KO2khJ8FydS6x1bWIKFnXqZWGWVgMT6sgpHIiTP6+46dij
Q3pKZv/KDxo1VdjtalhJhA5HC7aJKpOUY/rjJml8738+Va5fgdL/4nBRZdlv2qZ3
gFU2P5pBN1mvaDNn2IuQcQO45GJ1r7RSCaJGKDqBgG7mkWsIJTaffeP9P3Ie3yms
p+0llupxAgMBAAECggEASe6WxsdnMAW21aKlXIvMQCPz9Arj13nc0JMedKCTh5iq
w7Q52HsXSpmhld0cvGaDyxIZcrZWQKCAfkHxAlOWkNCsusq40rJRvbUSpYDjFKxA
B+DcehhIOB8QPBrE+Nd4PzfqSt8Bj4EE6qs/zUgOV1nwW4jhuVCxzm/R5IDPPJQF
QYYaYJ+mt/nwU8eTRkYH+miF5XC1Mc7rm+4qhOMtIQpz3rJ/e+7mCHcvgyqachMS
Rmn2ob1rhedG4MPMnGIEvVXSaJp+PbLWqpEGiwosxHuriXTp34i+w6u/TXRBAebR
2FJREpydnGnILyuHeYll6FsUoPbkUGSezDHos2N3gQKBgQDY69k0um5Tc1ZT+xQZ
YJHs/kLBlYqSP7Tqfh4RM0LabHTXCKV9xMU7PSE4T4L1+yYAc+BaNniRoZ1rWYDy
0gKX9ZQsRpaO/MHmZXCP8ProfHEp9ksPMzeVrznixOVsFsXRwMrzfelj3u/8FO+e
9flMzTuYUVfkIKhRslXL/3ljuwKBgQDBmZEpXq+PQuj7vJxFG7DulBpmEpc1BuZ1
9KmnVJh6wT8btezV5xBu8YGxIKJKJwBgbV/fz3PCv7MKq+FgBgUvHpmR/jeEfBn1
5nMxeUQ18Mz7Qh+/WRQUAQUtxTsF/PBe1Js9Bfow1hamMN8sjA/nudfRHT5STFnv
r0ousYUpwwKBgQDIRObiEYUuMnvvSQ2DaG8RWKWVSFUJNRv4qrtjsen08tDkfq0b
coGNcaCr9my4YUjyfgkSANxLx+QoahftFwsh9K76OaljFeSGq/zCR/++iLWJwkQG
Iokc/wv4W0RdCKFWSCKXrxW9COST3VhlcD7cM1bkB6WXjrcXxggEMTeHZQKBgEWN
n2pGezoz5rIyM85cQXelO8q6GRrS7M8p5B2ateE8NikFdh3vEqdZGqgBOmz7kBYU
lWI6DLPF3VQTJjgyUYB5NOEPfPCbCVSfw3jdwVTDu1D1k6RSI3WAXFTyS9893Tmk
llUZJaaP87knS7acZ0ZwLQoSwIYeqh0DU9psqpOzAoGANgQF/MYhH7EaoWGBpnLa
gjSOvTAAiKJJKiypwa0p5fTFyOJE8azgP8lW76iy4R/JnfileYx71OnhkehMjniB
jcmbDBOgcCwte4FlhRCAdcCt7F9Q6FV3ctZaF8aErfipU67UcatjDOTUWE5mkzrS
RyOmk+GfQDBN4gRfE2reA+I=
-----END PRIVATE KEY-----
PEM;

    public function test_verifies_payload_signed_with_matching_private_key(): void
    {
        [$privatePem, $publicPem] = $this->keyPair();
        $body = '{"event_type":"transfers#state-change","sent_at":"2026-01-01T12:34:56Z"}';

        $verifier = new WebhookSignatureVerifier([$publicPem]);

        self::assertTrue($verifier->verify($body, $this->sign($body, $privatePem)));
    }

    public function test_rejects_payload_that_does_not_match_the_signature(): void
    {
        [$privatePem, $publicPem] = $this->keyPair();
        $signature = $this->sign('{"amount":10}', $privatePem);

        $verifier = new WebhookSignatureVerifier([$publicPem]);

        self::assertFalse($verifier->verify('{"amount":100}', $signature));
    }

    public function test_rejects_payload_that_was_re_encoded_after_signing(): void
    {
        [$privatePem, $publicPem] = $this->keyPair();
        $rawBody = '{"amount": 10,"currency":"EUR"}';
        $signature = $this->sign($rawBody, $privatePem);
        $reEncodedBody = (string) json_encode(['amount' => 10, 'currency' => 'EUR']);

        $verifier = new WebhookSignatureVerifier([$publicPem]);

        self::assertTrue($verifier->verify($rawBody, $signature));
        self::assertFalse($verifier->verify($reEncodedBody, $signature));
    }

    public function test_rejects_signature_created_with_a_different_private_key(): void
    {
        [$privatePem] = $this->keyPair();
        [, $otherPublicPem] = $this->keyPair(self::ROTATED_TEST_PRIVATE_KEY);
        $body = '{"event_type":"transfers#state-change"}';

        $verifier = new WebhookSignatureVerifier([$otherPublicPem]);

        self::assertFalse($verifier->verify($body, $this->sign($body, $privatePem)));
    }

    public function test_accepts_signature_matching_any_of_the_configured_keys(): void
    {
        [$privatePem, $publicPem] = $this->keyPair();
        [, $rotatedPublicPem] = $this->keyPair(self::ROTATED_TEST_PRIVATE_KEY);
        $body = '{"event_type":"transfers#state-change"}';

        $verifier = new WebhookSignatureVerifier([$rotatedPublicPem, $publicPem]);

        self::assertTrue($verifier->verify($body, $this->sign($body, $privatePem)));
    }

    public function test_rejects_empty_and_malformed_signatures(): void
    {
        [$privatePem, $publicPem] = $this->keyPair();
        $body = '{"event_type":"transfers#state-change"}';
        $signature = $this->sign($body, $privatePem);

        $verifier = new WebhookSignatureVerifier([$publicPem]);

        foreach (['', '   ', 'not-base64!!', 'sha256='.$signature, substr($signature, 0, 20)] as $invalid) {
            self::assertFalse(
                $verifier->verify($body, $invalid),
                'Signature "'.$invalid.'" should not verify.',
            );
        }
    }

    public function test_accepts_surrounding_whitespace_in_the_signature_header(): void
    {
        [$privatePem, $publicPem] = $this->keyPair();
        $body = '{"event_type":"transfers#state-change"}';

        $verifier = new WebhookSignatureVerifier([$publicPem]);

        self::assertTrue($verifier->verify($body, ' '.$this->sign($body, $privatePem)."\n"));
    }

    public function test_published_public_keys_are_valid_pems(): void
    {
        self::assertNotFalse(openssl_pkey_get_public(WebhookSignatureVerifier::SANDBOX_PUBLIC_KEY));
        self::assertNotFalse(openssl_pkey_get_public(WebhookSignatureVerifier::PRODUCTION_PUBLIC_KEY));
    }

    public function test_sandbox_and_production_factories_verify_with_the_published_keys(): void
    {
        $sandbox = WebhookSignatureVerifier::sandbox();
        $production = WebhookSignatureVerifier::production();
        $body = '{"event_type":"transfers#state-change"}';
        $signature = base64_encode('not-a-real-signature');

        self::assertFalse($sandbox->verify($body, $signature));
        self::assertFalse($production->verify($body, $signature));
    }

    public function test_constructor_requires_at_least_one_key(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one webhook public key is required.');

        new WebhookSignatureVerifier([]);
    }

    public function test_constructor_rejects_invalid_pem(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('valid PEM encoded public key');

        new WebhookSignatureVerifier(['not-a-pem']);
    }

    public function test_constructor_rejects_blank_pem(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('valid PEM encoded public key');

        new WebhookSignatureVerifier(['   ']);
    }

    /**
     * @return array{0: string, 1: string} Private and public PEM of an embedded test keypair.
     */
    private function keyPair(string $privatePem = self::TEST_PRIVATE_KEY): array
    {
        $key = openssl_pkey_get_private($privatePem);
        if ($key === false) {
            self::fail('Unable to load the embedded test RSA private key.');
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            self::fail('Unable to read the embedded test RSA keypair.');
        }

        return [$privatePem, $details['key']];
    }

    private function sign(string $body, string $privatePem): string
    {
        $signature = '';
        $signed = openssl_sign($body, $signature, $privatePem, OPENSSL_ALGO_SHA256);
        if ($signed !== true) {
            self::fail('Unable to sign the test payload.');
        }

        return base64_encode($signature);
    }
}
