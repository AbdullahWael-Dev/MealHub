<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AI\RecommendMealRequest;
use App\Http\Resources\Api\V1\RecommendationResource;
use App\Services\V1\AI\RecommendationService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService
    ) {
    }

    public function recommend(RecommendMealRequest $request): JsonResponse
    {
        $result = $this->recommendationService->recommend(
            $request->validated()['message']
        );

        return response()->json([
            'success'         => $result['success'],
            'message'         => $result['message'],
            'recommendations' => RecommendationResource::collection($result['recommendations']),
        ], $result['success'] ? 200 : 503);
    }
}