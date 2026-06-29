<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MealImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'image_path',
        'is_primary',
        'sort_order',
        'alt_text',
    ];
    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image_path ? asset('storage/' . $this->image_path) : null,
        );
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function (MealImage $image) {
            if ($image->is_primary) {
                static::where('meal_id', $image->meal_id)
                    ->where('id', '!=', $image->id)
                    ->update(['is_primary' => false]);
            }
        });

        static::deleting(function (MealImage $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }
}
