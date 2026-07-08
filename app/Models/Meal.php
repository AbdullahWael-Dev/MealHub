<?php

namespace App\Models;

use App\Traits\HasUniqueSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Meal extends Model
{
    use HasFactory, SoftDeletes, HasUniqueSlug;
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock_quantity',
        'is_available',
        'is_featured',
        'preparation_time',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'stock_quantity' => 'integer',
        'review_count' => 'integer',
        'preparation_time' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MealImage::class);
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(MealImage::class)->where('is_primary', true);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
    protected function displayImage(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->images->firstWhere('is_primary', true)
                ?? $this->images->sortBy('sort_order')->first(),
        );
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    protected function finalPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->discount_price ?? $this->price,
        );
    }
    protected function hasDiscount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->discount_price !== null && $this->discount_price < $this->price,
        );
    }

    protected static function boot()
    {
        parent::boot();
        static::forceDeleting(function (Meal $meal) {
            $meal->images->each(fn($image) => $image->delete());
        });
        static::deleting(function (Meal $meal) {
            $meal->favorites()->delete();
        });
    }
}
