<?php

namespace App\Enums;

enum AiProvider: string
{
    case Claude   = 'claude';
    case OpenAI   = 'openai';
    case DeepSeek = 'deepseek';
    case Groq     = 'groq';
    case Custom   = 'custom';
}
