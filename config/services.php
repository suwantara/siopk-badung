<?php

return [

    /*
    |------------------------------------------------------------------
    | AI Provider — Multi-provider support
    |------------------------------------------------------------------
    | Provider yang didukung:
    |   claude (Anthropic), openai, deepseek, groq, custom
    |
    | Set AI_PROVIDER di .env untuk memilih provider.
    | Masing-masing provider punya API key, URL, model, dan timeout sendiri.
    |
    | Untuk custom provider (misal: Ollama, LM Studio, OpenRouter, dll):
    |   set AI_PROVIDER=custom dan isi CUSTOM_AI_* variables.
    |   CUSTOM_AI_TYPE: openai (default) atau claude (response format)
    */
    'ai' => [
        'provider' => env('AI_PROVIDER', 'claude'),

        'claude' => [
            'api_key'    => env('CLAUDE_API_KEY', ''),
            'api_url'    => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages'),
            'model'      => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
            'max_tokens' => env('CLAUDE_MAX_TOKENS', 1024),
            'timeout'    => env('CLAUDE_TIMEOUT', 30),
        ],

        'openai' => [
            'api_key'    => env('OPENAI_API_KEY', ''),
            'api_url'    => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
            'model'      => env('OPENAI_MODEL', 'gpt-4o'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 1024),
            'timeout'    => env('OPENAI_TIMEOUT', 30),
        ],

        'deepseek' => [
            'api_key'    => env('DEEPSEEK_API_KEY', ''),
            'api_url'    => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions'),
            'model'      => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 1024),
            'timeout'    => env('DEEPSEEK_TIMEOUT', 30),
        ],

        'groq' => [
            'api_key'    => env('GROQ_API_KEY', ''),
            'api_url'    => env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
            'model'      => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'max_tokens' => env('GROQ_MAX_TOKENS', 1024),
            'timeout'    => env('GROQ_TIMEOUT', 30),
        ],

        'custom' => [
            'api_key'    => env('CUSTOM_AI_API_KEY', ''),
            'api_url'    => env('CUSTOM_AI_API_URL', ''),
            'model'      => env('CUSTOM_AI_MODEL', ''),
            'max_tokens' => env('CUSTOM_AI_MAX_TOKENS', 1024),
            'timeout'    => env('CUSTOM_AI_TIMEOUT', 30),
            'type'       => env('CUSTOM_AI_TYPE', 'openai'),
        ],
    ],

    /*
    |------------------------------------------------------------------
    | WhatsApp Notification (opsional, Fase 7)
    |------------------------------------------------------------------
    */
    'whatsapp' => [
        'token'        => env('FONNTE_TOKEN', ''),
        'country_code' => env('FONNTE_COUNTRY_CODE', '62'),
        'admin_wa'     => env('FONNTE_ADMIN_WA', ''),
    ],

    /*
    | Layanan lain (bawaan Laravel)
    */
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
