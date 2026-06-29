<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MealIndexRequest;
use App\Http\Resources\Api\V1\MealResource;
use App\Services\V1\MealServices\MealService;

class MealController extends Controller
{
    public function __construct(protected MealService $mealService) {}

    public function index(MealIndexRequest $request)
    {
        $meals = $this->mealService->paginate($request);

        return MealResource::collection($meals);
    }

    public function show(string $slug)
    {
        $meal = $this->mealService->findBySlug($slug);

        return new MealResource($meal);
    }
}