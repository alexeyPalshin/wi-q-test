<?php

namespace GreatFood\Http;

interface HttpClientInterface
{
    public function send(HttpRequest $request): HttpResponse;
}