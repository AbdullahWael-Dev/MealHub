<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\V1\Review\StoreReviewRequest;
use App\Http\Requests\Api\V1\Review\UpdateReviewRequest;
use App\Http\Resources\Api\V1\MealReviewResource;
use App\Models\Meal;
use App\Models\review;
use App\Services\V1\ReviewServices\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReviewController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ReviewService $reviewService)
    {
    }

    public function store(StoreReviewRequest $request, int $mealId): JsonResponse
    {
        $review = $this->reviewService->createReview(
            $request->user(),
            $mealId,
            $request->validated()
        );
        return $this->successResponse(new ReviewResource($review), 'Review created successfully.', 201);
    }
     public function index(Request $request, int $meal): JsonResponse
    {
        Meal::findOrFail($meal);
        $reviews = Review::where('meal_id', $meal)
            ->with('user')
            ->latest()
            ->paginate();
        return $this->successResponse([
            'reviews' => MealReviewResource::collection($reviews->items()),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ], 'Reviews retrieved successfully.');
    }
     public function show(int $review): JsonResponse
    {
        $review = Review::with(['user', 'meal'])->findOrFail($review);
        return $this->successResponse(
            new ReviewResource($review),
            'Review retrieved successfully.'
        );
    }
      public function update(UpdateReviewRequest $request, int $review): JsonResponse
    {
        $review = $this->reviewService->updateReview(
            $request->user(),
            $review,
            $request->validated()
        );

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review updated successfully.'
        );
    }
    public function destroy(Request $request, int $review): Response
    {
        $this->reviewService->deleteReview($request->user(), $review);
        return response()->noContent();
    }
}