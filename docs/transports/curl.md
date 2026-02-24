# Curl Transport Recipe

This guide shows a simple custom curl transport implementation.

Note: the SDK does not require curl. This is an optional adapter pattern.

## Optional install
```bash
composer require guzzlehttp/psr7
```

## Example transport
```php
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\TransportException;

final class CurlTransport implements TransportInterface
{
    public function send(RequestInterface $request): ResponseInterface
    {
        $ch = curl_init((string) $request->getUri());
        if ($ch === false) {
            throw new TransportException('Failed to initialize curl handle.');
        }

        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[] = $name.': '.implode(', ', $values);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => (string) $request->getBody(),
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new TransportException('Curl request failed: '.$error);
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 500;
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE) ?: 0;
        $headerRaw = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);
        curl_close($ch);

        $responseHeaders = [];
        foreach (explode("\r\n", trim($headerRaw)) as $line) {
            $pos = strpos($line, ':');
            if ($pos === false) {
                continue;
            }
            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $responseHeaders[$name][] = $value;
        }

        return new Response((int) $status, $responseHeaders, $body);
    }
}
```

## Use with SDK
```php
$wise = Wise::client($config, new CurlTransport(), $requestFactory, $streamFactory);
```

## Safety notes
- Always set timeout and connect-timeout.
- Do not log Authorization headers.
- Prefer idempotency key + retry middleware for write retries.
