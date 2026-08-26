<?php

namespace App\Services\V1\AI;

use App\Models\Meal;
use App\Services\V1\AI\AIService;
use Illuminate\Support\Collection;

class RecommendationService
{
    protected const MAX_RESULTS = 5;

    public function __construct(
        protected AIService $aiService
    ) {
    }

    public function recommend(string $userMessage): array
    {
        $intent = $this->aiService->getRecommendationIntent($userMessage);
        if (!$intent['success']) {
            return [
                'success'         => false,
                'message'         => 'I could not understand your request right now. Please try again or rephrase your message.',
                'recommendations' => collect(),
            ];
        }

        $data = $this->normalizePriceIntent($userMessage, $intent['data']);
        $data['prompt_keywords'] = $this->extractPromptKeywords($userMessage);

        $meals = $this->queryAvailableMeals($data);

        if ($meals->isEmpty()) {
            return [
                'success'         => true,
                'message'         => 'There are no meals matching your request at the moment.',
                'recommendations' => collect(),
            ];
        }

        $ranked = $this->rankMeals($meals, $data);

        $recommendations = $ranked
            ->take(self::MAX_RESULTS)
            ->map(fn (Meal $meal) => [
                'meal'   => $meal,
                'reason' => $this->buildReason($meal, $data),
            ])
            ->values();

        return [
            'success'         => true,
            'message'         => null,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * يجيب الوجبات المتاحة فعليًا من الداتابيز، ويطبق فلاتر AI
     * (budget/category) لكن بعد التحقق منها في Laravel.
     *
     * ملاحظة: أسماء الأعمدة هنا (is_available, quantity, price, discount_price,
     * category) افتراضية حسب الخطة اللي انت كتبتها. لو أسماء الأعمدة
     * عندك مختلفة، عدّلها هنا بس - باقي الـ Service مش هيتأثر.
     */
    protected function normalizePriceIntent(string $userMessage, array $data): array
    {
        $message = strtolower(trim($userMessage));

        // Range check runs FIRST and short-circuits everything below.
        // This prevents an explicit two-sided range like "from 50 to 100"
        // from being collapsed into a one-sided min/max, which happened
        // before because "from" also matches the minimum-only keyword list.
        $range = $this->extractPriceRangeFromMessage($message);

        if (!is_null($range)) {
            $data['budget_min'] = $range['min'];
            $data['budget_max'] = $range['max'];

            return $data;
        }

        $budgetMin = $data['budget_min'] ?? null;
        $budgetMax = $data['budget_max'] ?? null;

        $minimumValue = $this->extractPriceValueFromMessage($message, [
            'more than', 'greater than', 'above', 'over', 'at least', 'minimum', 'starting from', 'from',
            'costing more than', 'cost more than', 'priced above', 'price more than',
        ]);

        $maximumValue = $this->extractPriceValueFromMessage($message, [
            'less than', 'under', 'below', 'up to', 'maximum', 'not more than', 'at most', 'budget of',
            'costing less than', 'cost less than', 'priced under', 'price under',
        ]);

        if (!is_null($minimumValue)) {
            $budgetMin = (int) round((float) $minimumValue);
            $budgetMax = null;
        }

        if (!is_null($maximumValue)) {
            $budgetMax = (int) round((float) $maximumValue);
            $budgetMin = null;
        }

        $isMinimumPriceRequest = !is_null($minimumValue)
            || preg_match('/\b(more than|greater than|above|over|at least|minimum|starting from|from\s+[0-9][0-9,]*)\b/i', $message)
            || preg_match('/>\s*\$?\d[\d,]*(?:\.\d+)?/i', $message)
            || preg_match('/\b(costing more than|cost more than|priced above|price more than)\b/i', $message);

        $isMaximumPriceRequest = !is_null($maximumValue)
            || preg_match('/\b(less than|under|below|up to|maximum|not more than|at most|budget of|under\s*\$?)\b/i', $message)
            || preg_match('/<\s*\$?\d[\d,]*(?:\.\d+)?/i', $message)
            || preg_match('/\b(costing less than|cost less than|priced under|price under)\b/i', $message);

        if ($isMinimumPriceRequest && is_null($budgetMin) && is_numeric($budgetMax)) {
            $budgetMin = (int) $budgetMax;
            $budgetMax = null;
        }

        if ($isMaximumPriceRequest && is_null($budgetMax) && is_numeric($budgetMin)) {
            $budgetMax = (int) $budgetMin;
            $budgetMin = null;
        }

        if ($isMinimumPriceRequest && !is_null($budgetMin)) {
            $budgetMax = null;
        }

        if ($isMaximumPriceRequest && !is_null($budgetMax)) {
            $budgetMin = null;
        }

        $data['budget_min'] = $budgetMin;
        $data['budget_max'] = $budgetMax;

        return $data;
    }

    /**
     * Detects an explicit two-sided range ("between 50 and 100", "from 50 to 100")
     * before any single-sided extraction runs, so the upper bound can never be
     * silently discarded by the "from" minimum-keyword match below.
     */
    protected function extractPriceRangeFromMessage(string $message): ?array
    {
        $patterns = [
            '/\bbetween\s*\$?\s*(\d[\d,]*(?:\.\d+)?)\s*(?:and|to|-)\s*\$?\s*(\d[\d,]*(?:\.\d+)?)/i',
            '/\bfrom\s*\$?\s*(\d[\d,]*(?:\.\d+)?)\s*to\s*\$?\s*(\d[\d,]*(?:\.\d+)?)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $a = (float) str_replace(',', '', $matches[1]);
                $b = (float) str_replace(',', '', $matches[2]);

                return [
                    'min' => (int) round(min($a, $b)),
                    'max' => (int) round(max($a, $b)),
                ];
            }
        }

        return null;
    }

    protected function extractPriceValueFromMessage(string $message, array $phrases): ?float
    {
        foreach ($phrases as $phrase) {
            $pattern = '/\b' . preg_quote($phrase, '/') . '\s*\$?\s*(\d[\d,]*(?:\.\d+)?)/i';
            if (preg_match($pattern, $message, $matches)) {
                return (float) str_replace(',', '', $matches[1]);
            }
        }

        return null;
    }

    protected function extractPromptKeywords(string $message): array
    {
        $normalized = preg_replace('/[^a-z0-9\s]/i', ' ', strtolower($message));
        $tokens = preg_split('/\s+/', trim((string) $normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $stopWords = [
            'i', 'want', 'a', 'an', 'the', 'with', 'and', 'or', 'for', 'of', 'to', 'from', 'my', 'me',
            'more', 'less', 'than', 'under', 'over', 'above', 'below', 'please', 'meal', 'food', 'costing',
            'price', 'priced', 'budget', 'under', 'more', 'about', 'around', 'like', 'need', 'hello', 'hi'
        ];

        return collect($tokens)
            ->filter(fn ($token) => !in_array($token, $stopWords, true))
            ->filter(fn ($token) => !is_numeric($token))
            ->values()
            ->all();
    }

    protected function queryAvailableMeals(array $intent): Collection
    {
        $query = Meal::query()
            ->where('is_available', true)
            ->where('stock_quantity', '>', 0);

        $budgetMin = $intent['budget_min'] ?? null;
        $budgetMax = $intent['budget_max'] ?? null;

        if (!is_null($budgetMin)) {
            $query->where(function ($q) use ($budgetMin) {
                $q->where(function ($q2) use ($budgetMin) {
                    $q2->whereNotNull('discount_price')
                        ->where('discount_price', '>=', $budgetMin);
                })->orWhere(function ($q2) use ($budgetMin) {
                    $q2->whereNull('discount_price')
                        ->where('price', '>=', $budgetMin);
                });
            });
        }

        if (!is_null($budgetMax)) {
            $query->where(function ($q) use ($budgetMax) {
                $q->where(function ($q2) use ($budgetMax) {
                    $q2->whereNotNull('discount_price')
                        ->where('discount_price', '<=', $budgetMax);
                })->orWhere(function ($q2) use ($budgetMax) {
                    $q2->whereNull('discount_price')
                        ->where('price', '<=', $budgetMax);
                });
            });
        }

        if (!is_null($intent['category'])) {
            $category = $intent['category'];
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', 'like', "%{$category}%");
            });
        }

        $preferences = collect($intent['preferences'] ?? [])
            ->map(fn ($p) => mb_strtolower(trim($p)))
            ->filter()
            ->all();

        $promptKeywords = collect($intent['prompt_keywords'] ?? [])
            ->map(fn ($p) => mb_strtolower(trim($p)))
            ->filter()
            ->all();

        $matchKeywords = $this->filterMatchKeywordsForFiltering(array_values(array_unique(array_merge($preferences, $promptKeywords))));

        if (!empty($matchKeywords)) {
            $query->where(function ($q) use ($matchKeywords) {
                foreach ($matchKeywords as $keyword) {
                    $normalizedKeyword = $this->normalizePreferenceKeyword($keyword);
                    $spicyKeywords = ['spicy', 'hot', 'chili', 'chilli', 'jalapeno', 'jalapeño', 'pepper', 'buffalo', 'fiery', 'spice'];

                    $effectiveKeywords = in_array($normalizedKeyword, ['spicy', 'hot'], true)
                        ? $spicyKeywords
                        : $this->expandKeywordTerms($keyword);

                    $q->orWhere(function ($nested) use ($effectiveKeywords) {
                        foreach ($effectiveKeywords as $term) {
                            $nested->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($term) . '%'])
                                ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($term) . '%']);
                        }
                    });
                }
            });
        }

        return $query->with('category')->get();
    }

    protected function rankMeals(Collection $meals, array $intent): Collection
    {
        $preferences = collect($intent['preferences'] ?? [])
            ->map(fn ($p) => mb_strtolower(trim($p)))
            ->filter()
            ->all();

        $promptKeywords = collect($intent['prompt_keywords'] ?? [])
            ->map(fn ($keyword) => mb_strtolower(trim($keyword)))
            ->filter()
            ->all();

        $allKeywords = array_values(array_unique(array_merge($preferences, $promptKeywords)));

        return $meals->sortByDesc(function (Meal $meal) use ($allKeywords) {
            $score = 0;
            $haystack = mb_strtolower($meal->name . ' ' . $meal->description);

            foreach ($allKeywords as $keyword) {
                $normalizedPref = $this->normalizePreferenceKeyword($keyword);

                if ($normalizedPref === 'spicy' || $normalizedPref === 'hot') {
                    $spicyKeywords = ['spicy', 'hot', 'chili', 'jalapeno', 'jalapeño', 'pepper', 'buffalo', 'fiery', 'spice', 'chilli'];

                    $matchedSpicyKeyword = collect($spicyKeywords)->first(fn ($term) => str_contains($haystack, $term));

                    if ($matchedSpicyKeyword) {
                        $score += 20;
                    }

                    if (str_contains($haystack, $keyword)) {
                        $score += 5;
                    }

                    continue;
                }

                $expandedTerms = $this->expandKeywordTerms($keyword);
                $matchedExpandedTerm = collect($expandedTerms)->first(fn ($term) => str_contains($haystack, $term));

                if ($matchedExpandedTerm) {
                    $score += 3;
                }
            }

            $score += (float) ($meal->avg_rating ?? 0) * 2;

            if (!is_null($meal->discount_price)) {
                $score += 1;
            }

            return $score;
        })->values();
    }

    protected function filterMatchKeywordsForFiltering(array $keywords): array
    {
        $allowed = [
            'spicy', 'hot', 'chili', 'chilli', 'jalapeno', 'jalapeño', 'pepper',
            'buffalo', 'fiery', 'vegan', 'vegetarian', 'halal', 'seafood', 'family',
            'kids', 'pasta', 'burger', 'pizza', 'chicken', 'salmon', 'seafood', 'wrap', 'rice', 'bowl'
        ];

        return array_values(array_unique(array_filter($keywords, function (string $keyword) use ($allowed) {
            $normalized = strtolower(trim($keyword));

            if (in_array($normalized, $allowed, true)) {
                return true;
            }

            $normalizedPreference = $this->normalizePreferenceKeyword($normalized);

            return in_array($normalizedPreference, ['spicy', 'hot', 'vegan', 'vegetarian', 'halal', 'seafood', 'family', 'pasta'], true);
        })));
    }

    protected function expandKeywordTerms(string $keyword): array
    {
        $normalized = strtolower(trim($keyword));

        $keywordMap = [
            'pasta' => ['pasta', 'fettuccine', 'linguine', 'spaghetti', 'alfredo', 'bolognese', 'penne', 'lasagna', 'rigatoni'],
            'pizza' => ['pizza', 'margherita', 'pepperoni', 'cheese pizza'],
            'burger' => ['burger', 'sandwich', 'bun'],
            'chicken' => ['chicken', 'grilled chicken', 'crispy chicken'],
            'salmon' => ['salmon', 'fillet'],
            'seafood' => ['seafood', 'shrimp', 'salmon', 'mussels', 'calamari'],
            'wrap' => ['wrap', 'flatbread', 'tortilla'],
            'rice' => ['rice', 'bowl'],
        ];

        return $keywordMap[$normalized] ?? [$normalized];
    }

    protected function normalizePreferenceKeyword(string $keyword): string
    {
        $keyword = strtolower(trim($keyword));

        $replacements = [
            'spicy' => 'spicy',
            'hot' => 'hot',
            'chili' => 'spicy',
            'chilli' => 'spicy',
            'jalapeno' => 'spicy',
            'jalapeño' => 'spicy',
            'pepper' => 'spicy',
            'buffalo' => 'spicy',
            'fiery' => 'spicy',
            'vegan' => 'vegan',
            'vegetarian' => 'vegetarian',
            'halal' => 'halal',
            'seafood' => 'seafood',
            'family' => 'family',
            'kids' => 'family',
            'pasta' => 'pasta',
            'fettuccine' => 'pasta',
            'linguine' => 'pasta',
            'alfredo' => 'pasta',
            'bolognese' => 'pasta',
        ];

        return $replacements[$keyword] ?? $keyword;
    }

    protected function buildReason(Meal $meal, array $intent): string
    {
        $parts = [];

        if (!is_null($intent['budget_max'])) {
            $parts[] = 'fits your budget';
        }

        if (!is_null($intent['category']) && $meal->category) {
            $parts[] = "from the {$meal->category->name} category";
        }

        if (!is_null($meal->avg_rating) && $meal->avg_rating >= 4) {
            $formattedRating = rtrim(rtrim(number_format((float) $meal->avg_rating, 2, '.', ''), '0'), '.');
            $parts[] = "rated {$formattedRating}/5";
        }

        if (!is_null($meal->discount_price)) {
            $parts[] = 'currently discounted';
        }

        if (empty($parts)) {
            return 'Recommended based on your request.';
        }

        return implode(' and ', $parts) . '.';
    }
}