<?php

namespace App\Services\Ai;

class DeepSeekProvider extends OpenAiCompatibleProvider
{
    private bool $thinking;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->thinking = ($config['thinking'] ?? 'disabled') === 'enabled';
    }

    protected function buildBody(string $prompt, int $maxTokens): array
    {
        $body = parent::buildBody($prompt, $maxTokens);
        $body['thinking'] = ['type' => $this->thinking ? 'enabled' : 'disabled'];

        return $body;
    }

    protected function extractContent(array $responseData): string
    {
        $message = $responseData['choices'][0]['message'] ?? [];

        $content = trim((string) ($message['content'] ?? ''));

        if ($content !== '') {
            return $content;
        }

        return trim((string) ($message['reasoning_content'] ?? ''));
    }
}
