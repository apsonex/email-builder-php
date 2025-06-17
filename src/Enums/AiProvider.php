<?php

namespace Apsonex\EmailBuilderPhp\Enums;

enum AiProvider: string
{
    case Anthropic = 'anthropic';
    case DeepSeek = 'deepseek';
    case OpenAI = 'openai';
    case Ollama = 'ollama';
    case Mistral = 'mistral';
    case Groq = 'groq';
    case XAI = 'xai';
    case Gemini = 'gemini';
    case VoyageAI = 'voyageai';
}
