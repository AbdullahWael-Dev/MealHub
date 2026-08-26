<?php

namespace App\Support;

/**
 * Canonical list of preference tags that the AI is allowed to return.
 * This is the single source of truth for AIService's prompt constraint
 * and validation — kept separate so it can be reused anywhere else
 * that needs to know "what preference values exist in this system"
 * without duplicating the literal array.
 *
 * Note: RecommendationService has its own broader synonym-matching logic
 * (normalizePreferenceKeyword / expandKeywordTerms / filterMatchKeywordsForFiltering)
 * because it also matches raw words typed by the user in free text
 * (e.g. "hot", "buffalo", "jalapeño") — that logic is intentionally
 * separate from this list and untouched.
 */
class MealPreferenceVocabulary
{
    protected const CANONICAL = [
        'spicy',
        'vegan',
        'vegetarian',
        'halal',
        'seafood',
        'family',
        'kids',
        'pasta',
        'pizza',
        'burger',
        'chicken',
        'salmon',
        'wrap',
        'rice',
        'bowl',
    ];

    /**
     * @return string[]
     */
    public static function canonical(): array
    {
        return self::CANONICAL;
    }

    public static function isCanonical(string $value): bool
    {
        return in_array(strtolower(trim($value)), self::CANONICAL, true);
    }

    /**
     * Comma-separated list for embedding in the AI system prompt.
     */
    public static function asPromptEnum(): string
    {
        return implode(', ', self::CANONICAL);
    }
}