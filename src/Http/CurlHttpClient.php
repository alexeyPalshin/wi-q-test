<?php

namespace GreatFood\Http;

use GreatFood\Exceptions\HttpException;

final class CurlHttpClient implements HttpClientInterface
{
    public function send(HttpRequest $request): HttpResponse
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            $response = $this->executeCurl($request);

            $status = $response->statusCode;


            switch (true) {
                // RATE LIMIT: 429 Too Many Requests
                case ($status === 429):
                    $retryAfter = $response->headers['Retry-After'] ?? null;

                    if ($retryAfter !== null && is_numeric($retryAfter)) {
                        sleep((int)$retryAfter);
                    } else {
                        $this->sleepWithBackoff($attempt);
                    }
                    break;

                case ($status >= 500 && $status < 600):
                    $this->sleepWithBackoff($attempt);
                    break;

                // success responses
                default:
                    return $response;
            }

            if ($attempt >= self::MAX_ATTEMPTS) {
                return $response;
            }
        }
    }

    private function executeCurl(HttpRequest $request): HttpResponse
    {
        $ch = curl_init($request->url);

        if ($ch === false) {
            throw new HttpException('Failed to initialize cURL');
        }

        $headers = [];
        foreach ($request->headers as $k => $v) {
            $headers[] = $k . ': ' . $v;
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        if ($request->body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
        }

        $raw = curl_exec($ch);

        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new HttpException('cURL error: ' . $error);
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $headerStr = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);

        curl_close($ch);

        $headersOut = $this->parseHeaders($headerStr);

        return new HttpResponse($status, $headersOut, $body);
    }

    /** @return array<string, string> */
    private function parseHeaders(string $raw): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($raw)) ?: [];
        $out = [];
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $out[trim($k)] = trim($v);
            }
        }
        return $out;
    }

    private function sleepWithBackoff(int $attempt): void
    {
        $jitter = random_int(0, 150);
        $delayMs = (self::ATTEMPT_DELAY_MS * $attempt) + $jitter;

        usleep($delayMs * 1000);
    }
}