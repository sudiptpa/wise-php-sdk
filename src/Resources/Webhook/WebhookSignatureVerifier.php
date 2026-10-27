<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Webhook;

use Theocode\Wise\Exceptions\ValidationException;

/**
 * Verifies the `X-Signature-SHA256` header of incoming Wise webhook events.
 *
 * Wise signs the raw request body with an RSA private key using a SHA-256 digest (PKCS#1 v1.5)
 * and Base64 encodes the signature. Verification requires one of Wise's published public keys,
 * which are exposed as {@see self::SANDBOX_PUBLIC_KEY} and {@see self::PRODUCTION_PUBLIC_KEY}.
 *
 * Pass more than one key to support Wise key rotation without downtime.
 *
 * @see https://docs.wise.com/guides/developer/webhooks/event-handling
 */
final class WebhookSignatureVerifier
{
    /**
     * Public key Wise publishes for sandbox webhook events.
     */
    public const SANDBOX_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\n"
        ."MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwpb91cEYuyJNQepZAVfP\n"
        ."ZIlPZfNUefH+n6w9SW3fykqKu938cR7WadQv87oF2VuT+fDt7kqeRziTmPSUhqPU\n"
        ."ys/V2Q1rlfJuXbE+Gga37t7zwd0egQ+KyOEHQOpcTwKmtZ81ieGHynAQzsn1We3j\n"
        ."wt760MsCPJ7GMT141ByQM+yW1Bx+4SG3IGjXWyqOWrcXsxAvIXkpUD/jK/L958Cg\n"
        ."nZEgz0BSEh0QxYLITnW1lLokSx/dTianWPFEhMC9BgijempgNXHNfcVirg1lPSyg\n"
        ."z7KqoKUN0oHqWLr2U1A+7kqrl6O2nx3CKs1bj1hToT1+p4kcMoHXA7kA+VBLUpEs\n"
        ."VwIDAQAB\n"
        ."-----END PUBLIC KEY-----\n";

    /**
     * Public key Wise publishes for production webhook events.
     */
    public const PRODUCTION_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\n"
        ."MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvO8vXV+JksBzZAY6GhSO\n"
        ."XdoTCfhXaaiZ+qAbtaDBiu2AGkGVpmEygFmWP4Li9m5+Ni85BhVvZOodM9epgW3F\n"
        ."bA5Q1SexvAF1PPjX4JpMstak/QhAgl1qMSqEevL8cmUeTgcMuVWCJmlge9h7B1CS\n"
        ."D4rtlimGZozG39rUBDg6Qt2K+P4wBfLblL0k4C4YUdLnpGYEDIth+i8XsRpFlogx\n"
        ."CAFyH9+knYsDbR43UJ9shtc42Ybd40Afihj8KnYKXzchyQ42aC8aZ/h5hyZ28yVy\n"
        ."Oj3Vos0VdBIs/gAyJ/4yyQFCXYte64I7ssrlbGRaco4nKF3HmaNhxwyKyJafz19e\n"
        ."HwIDAQAB\n"
        ."-----END PUBLIC KEY-----\n";

    /**
     * @var list<string>
     */
    private array $publicKeysPem;

    /**
     * @param  list<string>  $publicKeysPem  One or more PEM encoded public keys. Listing several keys allows
     *                                       verification to continue while Wise rotates its signing key.
     *
     * @throws ValidationException when the list is empty or contains a key that is not a valid PEM.
     */
    public function __construct(array $publicKeysPem)
    {
        if ($publicKeysPem === []) {
            throw new ValidationException('At least one webhook public key is required.');
        }

        foreach ($publicKeysPem as $pem) {
            if (trim($pem) === '' || openssl_pkey_get_public($pem) === false) {
                throw new ValidationException('Webhook public key must be a valid PEM encoded public key.');
            }
        }

        $this->publicKeysPem = array_values($publicKeysPem);
    }

    /**
     * Verifier configured with Wise's sandbox public key.
     */
    public static function sandbox(): self
    {
        return new self([self::SANDBOX_PUBLIC_KEY]);
    }

    /**
     * Verifier configured with Wise's production public key.
     */
    public static function production(): self
    {
        return new self([self::PRODUCTION_PUBLIC_KEY]);
    }

    /**
     * Verify a raw webhook body against the Base64 encoded `X-Signature-SHA256` header value.
     *
     * Returns `false` for empty or malformed input and never throws, so callers can reject
     * unverifiable deliveries with a plain `if (! $verifier->verify(...))` check.
     */
    public function verify(string $rawBody, string $signatureBase64): bool
    {
        $signature = trim($signatureBase64);
        if ($signature === '') {
            return false;
        }

        $decoded = base64_decode($signature, true);
        if ($decoded === false || $decoded === '') {
            return false;
        }

        foreach ($this->publicKeysPem as $pem) {
            $key = openssl_pkey_get_public($pem);
            if ($key === false) {
                continue;
            }

            if (openssl_verify($rawBody, $decoded, $key, OPENSSL_ALGO_SHA256) === 1) {
                return true;
            }
        }

        return false;
    }
}
