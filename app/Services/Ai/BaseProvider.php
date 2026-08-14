<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseProvider implements AiProviderInterface
{
    protected string $apiKey;

    protected string $apiUrl;

    protected string $model;

    protected int $timeout;

    protected int $retries;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiUrl = $config['api_url'] ?? '';
        $this->model = $config['model'] ?? '';
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->retries = (int) ($config['retries'] ?? 3);
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->apiUrl);
    }

    public function analyze(string $prompt, int $maxTokens): array
    {
        try {
            $started = microtime(true);

            $response = Http::timeout($this->timeout)
                ->connectTimeout(10)
                ->retry($this->retries, fn (int $attempt) => $attempt * 500)
                ->withHeaders($this->headers())
                ->post($this->apiUrl, $this->buildBody($prompt, $maxTokens));

            if ($response->successful()) {
                $content = $this->extractContent($response->json());

                return ['success' => true, 'content' => trim($content)];
            }

            Log::error('AI Provider Error ['.static::class.']', [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 500),
                'elapsed' => round(microtime(true) - $started, 1),
            ]);

            return ['success' => false, 'content' => '', 'error' => $response->body()];

        } catch (\Exception $e) {
            Log::error('AI Provider Exception ['.static::class.']', ['message' => $e->getMessage()]);

            return ['success' => false, 'content' => '', 'error' => $e->getMessage()];
        }
    }

    abstract protected function headers(): array;

    abstract protected function buildBody(string $prompt, int $maxTokens): array;

    abstract protected function extractContent(array $responseData): string;
}
