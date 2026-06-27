<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    public function analyze(string $prompt, int $maxTokens): array;

    public function isAvailable(): bool;
}
