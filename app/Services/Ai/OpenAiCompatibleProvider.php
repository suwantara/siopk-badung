<?php

namespace App\Services\Ai;

abstract class OpenAiCompatibleProvider extends BaseProvider
{
    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'content-type'  => 'application/json',
        ];
    }

    protected function buildBody(string $prompt, int $maxTokens): array
    {
        return [
            'model'       => $this->model,
            'max_tokens'  => $maxTokens,
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];
    }

    protected function extractContent(array $responseData): string
    {
        return $responseData['choices'][0]['message']['content'] ?? '';
    }
}
