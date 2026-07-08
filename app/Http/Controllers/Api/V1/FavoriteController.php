<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAddressRequest;
use App\Http\Requests\Api\V1\StoreFavoriteRequest;
use App\Http\Requests\Api\V1\UpdateAddressRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Http\Resources\Api\V1\FavoriteResource;
use App\Services\V1\FavoriteServices\FavoriteService;
use App\Models\Address;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{

    use ApiResponseTrait;
    public function __construct(protected FavoriteService $favoriteService) {}

   public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $favorites = $this->favoriteService->list($request->user(), $perPage);

        return $this->successResponse(FavoriteResource::collection($favorites), 'Favorites retrieved successfully');
    }

    public function store(StoreFavoriteRequest $request)
    {
        try{
            $favorite = $this->favoriteService->add(
            $request->user(),
            (int) $request->validated('meal_id')
            );
        } catch(ValidationException $e) {
            return $this->errorResponse($e->errors(), $e->getMessage() ?? 'Validation Error', 422);
        }

       return $this->successResponse(new FavoriteResource($favorite), 'Meal added to favorites', 201);
    }

    public function destroy(Request $request, int $meal)
    {
        try {
            $this->favoriteService->remove($request->user(), $meal);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse([], $e->getMessage() ?? 'Meal not found in favorites', 404);
        }

        return $this->successResponse(null, 'Meal removed from favorites');
    }
      public function check(Request $request, int $meal)
    {
        $isFavorited = $this->favoriteService->isFavorited($request->user(), $meal);

        return $this->successResponse(
            ['is_favorited' => $isFavorited],
            'Check if meal is favorited'
        );
    }
}
