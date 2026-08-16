<?php

namespace App\Services\V1\ReviewServices;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\Meal;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function createReview(User $user,int $mealId, array $data): Review
    {
        return DB::transaction(function () use ($user, $mealId , $data) {

            $order = $this->resolveDeliveredOrderForUser($user, $data['order_id']);

            $this->assertMealBelongsToOrder($order, $mealId);

            $this->assertNotAlreadyReviewed($mealId, $order->id);

            $review = Review::create([
                'user_id' => $user->id,
                'meal_id' => $mealId,
                'order_id' => $order->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recalculateMealRating($mealId);

            return $review;
        });
    }

    public function updateReview(User $user, int $reviewId, array $data): Review
    {
        return DB::transaction(function () use ($user, $reviewId, $data) {

            /** @var Review $review */
            $review = $user->reviews()->lockForUpdate()->findOrFail($reviewId);

            $review->fill([
                'rating' => $data['rating'] ?? $review->rating,
                'comment' => array_key_exists('comment', $data) ? $data['comment'] : $review->comment,
            ]);

            $review->save();

            $this->recalculateMealRating($review->meal_id);

            return $review->fresh();
        });
    }

    public function deleteReview(User $user, int $reviewId): void
    {
        DB::transaction(function () use ($user, $reviewId) {
            $review = $user->reviews()->lockForUpdate()->findOrFail($reviewId);
            $mealId = $review->meal_id;
            $review->delete();
            $this->recalculateMealRating($mealId);
        });
    }
    private function resolveDeliveredOrderForUser(User $user, int $orderId): Order
    {
        $order = $user->orders()->with('items')->findOrFail($orderId);
        if ($order->status !== OrderStatus::Delivered) {
            abort(422, 'You can only review meals from delivered orders.');
        }
        return $order;
    }
    private function assertMealBelongsToOrder(Order $order, int $mealId): void
    {
        $mealInOrder = $order->items->contains('meal_id', $mealId);

        if (! $mealInOrder) {
            abort(422, 'This meal was not part of the selected order.');
        }
    }
    private function assertNotAlreadyReviewed(int $mealId, int $orderId): void
    {
        $alreadyReviewed = Review::where('meal_id', $mealId)
            ->where('order_id', $orderId)
            ->exists();

        if ($alreadyReviewed) {
            abort(422, 'You have already reviewed this meal for this order.');
        }
    }
    private function recalculateMealRating(int $mealId): void
    {
        $meal = Meal::whereKey($mealId)->lockForUpdate()->firstOrFail();

        $stats = Review::where('meal_id', $mealId)
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as avg_rating')
            ->first();
        $meal->review_count = (int) $stats->review_count;
        $meal->avg_rating = $stats->review_count > 0
            ? round((float) $stats->avg_rating, 2)
            : 0;
        $meal->save();
    }
}
