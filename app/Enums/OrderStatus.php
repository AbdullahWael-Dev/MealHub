<?php 

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}