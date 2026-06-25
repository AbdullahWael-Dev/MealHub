<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Services\V1\CategoryServices\CategoryService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $categories = $this->categoryService->getAll($request->only(['search', '    ', 'sort_by', 'sort_direction']), $request->input('per_page', 10));
        return $this->successResponse(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }
    public function show(string $slug) : JsonResponse
    {
        $category = $this->categoryService->getCategoryBySlug($slug);
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        return $this->successResponse(new CategoryResource($category), 'Category retrieved successfully');
    }
}
