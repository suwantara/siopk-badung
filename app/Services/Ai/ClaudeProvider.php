<?php

namespace App\Services\Ai;

class ClaudeProvider extends BaseProvider
{
    protected function headers(): array
    {
        return [
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ];
    }

    protected function buildBody(string $prompt, int $maxTokens): array
    {
        return [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];
    }

    protected function extractContent(array $responseData): string
    {
        return $responseData['content'][0]['text'] ?? '';
    }
}
