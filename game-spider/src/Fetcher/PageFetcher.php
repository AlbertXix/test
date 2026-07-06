<?php

namespace GameSpider\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PageFetcher
{
    private Client $client;
    private int $maxRetries;
    private int $retryDelay;

    public function __construct(?Client $client = null, int $maxRetries = 3, int $retryDelay = 1000)
    {
        $this->client = $client ?? new Client([
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
            ],
        ]);
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;
    }

    public function fetch(string $url): string
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts <= $this->maxRetries) {
            if ($attempts > 0) {
                echo "    Retry {$attempts}/{$this->maxRetries}...\n";
                usleep($this->retryDelay * 1000);
            }

            try {
                $response = $this->client->get($url);
                return (string) $response->getBody();
            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempts++;
            }
        }

        throw new \RuntimeException("Failed to fetch URL after {$this->maxRetries} retries: {$url} - {$lastException?->getMessage()}", 0, $lastException);
    }

    public function fetchJson(string $url, array $query = []): array
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts <= $this->maxRetries) {
            if ($attempts > 0) {
                echo "    Retry {$attempts}/{$this->maxRetries}...\n";
                usleep($this->retryDelay * 1000);
            }

            try {
                $options = [
                    'headers' => [
                        'Accept' => 'application/json, text/plain, */*',
                    ],
                ];
                if (!empty($query)) {
                    $options['query'] = $query;
                }
                $response = $this->client->get($url, $options);
                $body = (string) $response->getBody();
                return json_decode($body, true) ?? [];
            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempts++;
            }
        }

        throw new \RuntimeException("Failed to fetch JSON after {$this->maxRetries} retries: {$url} - {$lastException?->getMessage()}", 0, $lastException);
    }
}
