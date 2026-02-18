<?php

namespace GreatFood\Http;

use GreatFood\Exceptions\HttpException;

final class CurlHttpClient implements HttpClientInterface
{
    public function send(HttpRequest $request): HttpResponse
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
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerStr = substr($raw, 0, $headerSize) ?: '';
        $body = substr($raw, $headerSize) ?: '';
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
}