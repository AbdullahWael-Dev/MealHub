<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'recipient_name',
        'phone',
        'city',
        'area',
        'street',
        'building',
        'floor',
        'apartment',
        'landmark',
        'notes',
        'latitude',
        'longitude',
        'is_default',
    ];
    protected $casts = [
        'is_default' => 'boolean',
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
