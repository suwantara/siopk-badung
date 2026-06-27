<?php

namespace App\Services\Ai;

class AiProviderFactory
{
    public static function make(string $provider): AiProviderInterface
    {
        $config = config("services.ai.{$provider}", []);

        if (empty($config['api_key'])) {
            $provider = 'claude';
            $config   = config('services.ai.claude', []);
        }

        return match ($provider) {
            'claude'   => new ClaudeProvider($config),
            'openai'   => new OpenAiProvider($config),
            'deepseek' => new DeepSeekProvider($config),
            'groq'     => new GroqProvider($config),
            'custom'   => new CustomProvider($config),
            default    => new ClaudeProvider($config),
        };
    }
}
