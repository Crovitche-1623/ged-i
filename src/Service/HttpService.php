<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpService
{
    private ?HttpClientInterface $client = null;
    private bool $authorized;

    public function __construct(private HttpClientInterface $baseClient) {}

    public function getHttpClient(bool $authorized = true): HttpClientInterface
    {
        // To prevent the client from being fetched again on each method call
        // we check if there is already one existing with the same authorized
        // flag.
        if ($this->client && $authorized === $this->authorized) {
            return $this->client;
        }

        $options = ['headers' => ['Accept' => 'application/json']];

        if ($authorized) {
            $options['auth_bearer'] = $this->baseClient
                ->request('POST', 'http://157.26.82.44:2240/token', [
                    'body' => [
                        'grant_type' => 'password',
                        'username' => 'fanny.roulin',
                        'password' =>'fanny.roulin'
                    ]
                ])
                ->toArray()['access_token'];
            $options['base_uri'] = 'http://157.26.82.44:2240/api/';
        } else {
            $options['base_uri'] = 'http://157.26.82.44:2240/';
        }

        // Set global value for next method call.
        $this->client = $this->baseClient->withOptions($options);
        $this->authorized = $authorized;

        return $this->client;
    }
}
