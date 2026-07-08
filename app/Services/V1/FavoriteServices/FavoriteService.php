<?php

namespace App\Services\V1\FavoriteServices;

use App\Models\User;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class FavoriteService
{
    public function list(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->favorites()
            ->whereHas('meal')
            ->with(['meal.images', 'meal.category'])
            ->latest()
            ->paginate($perPage);
    }

    public function add(User $user, int $mealId): Favorite
    {
        $exists = Favorite::where('user_id', $user->id)
            ->where('meal_id', $mealId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'meal_id' => 'The meal is already in your favorites.',
            ]);
        }

        try {
            return Favorite::create([
                'user_id' => $user->id,
                'meal_id' => $mealId,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw ValidationException::withMessages([
                    'meal_id' => 'The meal is already in your favorites.',
                ]);
            }

            throw $e;
        }
    }

    public function remove(User $user, int $mealId): void
    {
        $favorite = Favorite::where('user_id', $user->id)
            ->where('meal_id', $mealId)
            ->first();

        if (! $favorite) {
            throw new ModelNotFoundException('The meal is not in your favorites.');
        }

        $favorite->delete();
    }

    public function isFavorited(User $user, int $mealId): bool
    {
        return Favorite::where('user_id', $user->id)
            ->where('meal_id', $mealId)
            ->exists();
    }
}
