<?php

namespace App\Services\Ai;

class CustomProvider extends BaseProvider
{
    private string $responseType;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->responseType = $config['type'] ?? 'openai';
    }

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
        if ($this->responseType === 'claude') {
            return $responseData['content'][0]['text'] ?? '';
        }

        return $responseData['choices'][0]['message']['content'] ?? '';
    }
}
