<?php

namespace App\Services\V1\CategoryServices;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    private const SORTABLE_COLUMNS = ['name', 'sort_order', 'created_at'];

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['is_active']);
            })
            ->when(
                in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true),
                function ($query) use ($filters) {
                    $direction = ($filters['sort_direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                    $query->orderBy($filters['sort_by'], $direction);
                },
                function ($query) {
                    $query->ordered();
                }
            )
            ->paginate($perPage);
    }

    public function getCategoryBySlug(string $slug): ?Category
    {
        return Category::query()->where('slug', $slug)->active()->first();
    }
}