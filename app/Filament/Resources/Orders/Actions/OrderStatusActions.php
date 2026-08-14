<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\v1\OrderServices\OrderService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OrderStatusActions
{
    public static function confirm(): Action
    {
        return Action::make('confirm')
            ->label('Confirm')
            ->icon('heroicon-o-check')
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->status === OrderStatus::Pending)
            ->action(function (Order $order) {
                app(OrderService::class)->updateStatus($order, OrderStatus::Confirmed);
                Notification::make()->title('Order confirmed.')->success()->send();
            });
    }

    public static function startPreparing(): Action
    {
        return Action::make('start_preparing')
            ->label('Start Preparing')
            ->icon('heroicon-o-fire')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->status === OrderStatus::Confirmed)
            ->action(function (Order $record) {
                app(OrderService::class)->updateStatus($record, OrderStatus::Preparing);
                Notification::make()->title('Order is now preparing.')->success()->send();
            });
    }

    public static function outForDelivery(): Action
    {
        return Action::make('out_for_delivery')
            ->label('Out for Delivery')
            ->icon('heroicon-o-truck')
            ->color('primary')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->status === OrderStatus::Preparing)
            ->action(function (Order $record) {
                app(OrderService::class)->updateStatus($record, OrderStatus::OutForDelivery);
                Notification::make()->title('Order is out for delivery.')->success()->send();
            });
    }

    public static function markDelivered(): Action
    {
        return Action::make('mark_delivered')
            ->label('Mark Delivered')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->status === OrderStatus::OutForDelivery)
            ->action(function (Order $record) {
                app(OrderService::class)->updateStatus($record, OrderStatus::Delivered);

                Notification::make()->title('Order marked as delivered.')->success()->send();
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Cancel')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => in_array(
                $record->status,
                [OrderStatus::Pending, OrderStatus::Confirmed],
                true
            ))
            ->action(function (Order $record) {
                app(OrderService::class)->cancelOrder($record->user, $record->id);
                Notification::make()->title('Order cancelled.')->success()->send();
            });
    }

    /**
     * @return array<Action>
     */
    public static function all(): array
    {
        return [
            self::confirm(),
            self::startPreparing(),
            self::outForDelivery(),
            self::markDelivered(),
            self::cancel(),
        ];
    }
}