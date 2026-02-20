<?php

namespace GreatFood\Http;

interface HttpClientInterface
{
    // Maximum number of retry attempts
    const MAX_ATTEMPTS = 3;

    // Delay in millisecond
    const ATTEMPT_DELAY_MS = 200;

    public function send(HttpRequest $request): HttpResponse;
}