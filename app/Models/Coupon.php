<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
    public function isPercent(): bool
    {
        return $this->type === 'percentage';
    }
     public function isFixed(): bool
    {
        return $this->type === 'fixed';
    }

    public function hasReachedLimit(): bool 
    {
        return $this->used_count >= $this->usage_limit;
    }
    public function isWithinValidityPeriod(): bool
    {
        $now = now();
        return $now->betweenIncluded($this->starts_at, $this->expires_at);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeByCode($query,string $code)
    {
        return $query->where('code', $code);
    }
}
