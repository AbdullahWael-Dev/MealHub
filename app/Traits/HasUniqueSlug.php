<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            $sourceField = $model->slugSourceField();
            $slugField = $model->slugField();

            if ($model->isDirty($sourceField) || empty($model->{$slugField})) {
                $model->{$slugField} = static::generateUniqueSlug(
                    $model->{$sourceField},
                    $slugField,
                    $model->id
                );
            }
        });
    }
    protected function slugSourceField(): string
    {
        return 'name';
    }
    protected function slugField(): string
    {
        return 'slug';
    }

    protected static function generateUniqueSlug(string $source, string $slugField, ?int $ignoreId = null): string
    {
        $slug = Str::slug($source);
        $originalSlug = $slug;
        $count = 1;

        while (
            static::where($slugField, $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}