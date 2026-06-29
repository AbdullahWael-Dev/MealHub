<?php

namespace App\Services\V1\MealServices;

use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MealService 
{
    public function paginate(Request $request) : LengthAwarePaginator
    {
        $query = Meal::query()->with(['category', 'images']);

        if($search = $request->input('search')) {
           $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if($categorySlug = $request->input('category')) {
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }
        if($request->has('is_available')) {
            $query->where('is_available',$request->boolean('is_available'));
        }
        if($request->has('is_featured')) {
            $query->where('is_featured',$request->boolean('is_featured'));
        }
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        return $query->orderBy($sortBy, $sortDirection)
                     ->paginate($request->input('per_page', 15));
    }
     public function findBySlug(string $slug): Meal
    {
        return Meal::with(['category', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();
    }
}