<?php

namespace App\Services\V1\AI;

use App\Support\MealPreferenceVocabulary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $model;
    protected int $timeout;
    protected int $retries;
    protected int $retryDelayMs;

    public function __construct()
    {
        $this->baseUrl      = config('services.ai.base_url');
        $this->apiKey       = config('services.ai.api_key');
        $this->model        = config('services.ai.model');
        $this->timeout      = config('services.ai.timeout');
        $this->retries      = config('services.ai.retries');
        $this->retryDelayMs = config('services.ai.retry_delay_ms');
    }

    public function getRecommendationIntent(string $userMessage): array
    {
        $userMessage = trim($userMessage);

        if ($userMessage === '') {
            return $this->failResponse('Empty user message.');
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->retry($this->retries, $this->retryDelayMs)
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.2,
                ]);

            if ($response->failed()) {
                Log::warning('AIService: request failed', [
                    'status' => $response->status(),
                ]);
                return $this->failResponse($this->mapHttpError($response->status()));
            }

            $rawContent = data_get($response->json(), 'choices.0.message.content');
            if (empty($rawContent)) {
                Log::warning('AIService: empty AI content in response');
                return $this->failResponse('Empty response from AI.');
            }

            $decoded = json_decode($rawContent, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                Log::warning('AIService: invalid JSON from AI', [
                    'json_error' => json_last_error_msg(),
                ]);
                return $this->failResponse('Invalid JSON returned by AI.');
            }

            return [
                'success' => true,
                'data'    => $this->validateAndNormalize($decoded),
                'error'   => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('AIService: connection/timeout error', ['message' => $e->getMessage()]);
            return $this->failResponse('AI service timeout.');
        } catch (\Throwable $e) {
            Log::error('AIService: unexpected error', ['message' => $e->getMessage()]);
            return $this->failResponse('Unexpected AI service error.');
        }
    }

    protected function systemPrompt(): string
    {
        $allowedPreferences = MealPreferenceVocabulary::asPromptEnum();

        return <<<PROMPT
You are the MealHub food recommendation assistant.
Your only task is to understand the user's request and convert it into a clean JSON object.

Strict rules:
- Do not invent meals that do not exist.
- Do not invent prices.
- Do not invent categories unless the user mentioned them or they are logically implied by the request.
- Do not invent IDs.
- Return JSON only. No extra text, no markdown, no explanations outside the JSON payload.
- If the user says 'more than', 'greater than', 'above', 'over', 'at least', or 'minimum', set budget_min to that number and leave budget_max as null.
- If the user says 'less than', 'under', 'below', 'up to', 'maximum', or 'budget', set budget_max to that number and leave budget_min as null.
- If the user says both a minimum and a maximum, set both values.
- The "preferences" array must contain ONLY values from this exact list: {$allowedPreferences}
- Map any related word the user uses to the closest value in that list
  (e.g. "hot", "chili", "jalapeño", "buffalo", "fiery" -> "spicy"; "kid-friendly" -> "kids"; "shrimp" -> "seafood").
- If nothing in the message maps to a value in that list, return an empty array. Never return a value outside the list.

Required JSON format:
{
  "budget_min": <number or null>,
  "budget_max": <number or null>,
  "category": "<string or null>",
  "preferences": ["..."],
  "reason": "<short sentence explaining the user's request>"
}

If the message is unclear or not related to food, return:
{
  "budget_min": null,
  "budget_max": null,
  "category": null,
  "preferences": [],
  "reason": "The request is unclear or not related to food"
}
PROMPT;
    }

    protected function validateAndNormalize(array $data): array
    {
        $budgetMin = $data['budget_min'] ?? null;
        if (!is_numeric($budgetMin)) {
            $budgetMin = null;
        } else {
            $budgetMin = (int) $budgetMin;
        }

        $budgetMax = $data['budget_max'] ?? null;
        if (!is_numeric($budgetMax)) {
            $budgetMax = null;
        } else {
            $budgetMax = (int) $budgetMax;
        }

        $category = $data['category'] ?? null;
        if (!is_string($category) || trim($category) === '') {
            $category = null;
        }

        $preferences = $data['preferences'] ?? [];
        if (!is_array($preferences)) {
            $preferences = [];
        } else {
            $normalized = array_map(
                fn ($p) => is_string($p) ? strtolower(trim($p)) : null,
                $preferences
            );

            // Defensive layer: even if the AI ignores the prompt instructions,
            // anything outside the canonical vocabulary is dropped here rather
            // than silently passed through to RecommendationService, where it
            // would be ignored anyway but skew the "reason"/matching logic.
            $preferences = array_values(array_unique(array_filter(
                $normalized,
                fn ($p) => $p !== null && MealPreferenceVocabulary::isCanonical($p)
            )));
        }

        $reason = $data['reason'] ?? null;
        if (!is_string($reason)) {
            $reason = null;
        }

        return [
            'budget_min'  => $budgetMin,
            'budget_max'  => $budgetMax,
            'category'    => $category,
            'preferences' => $preferences,
            'reason'      => $reason,
        ];
    }

    protected function failResponse(string $message): array
    {
        return [
            'success' => false,
            'data'    => [
                'budget_min'  => null,
                'budget_max'  => null,
                'category'    => null,
                'preferences' => [],
                'reason'      => null,
            ],
            'error' => $message,
        ];
    }

    protected function mapHttpError(int $status): string
    {
        return match (true) {
            $status === 401 => 'AI service authentication failed.',
            $status === 429 => 'AI service rate limit exceeded.',
            $status >= 500  => 'AI service is currently unavailable.',
            default         => 'AI service request failed.',
        };
    }
}