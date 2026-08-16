<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Review #')
                            ->formatStateUsing(fn($state) => "#{$state}")
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-o-hashtag'),

                        TextEntry::make('rating')
                            ->label('Rating')
                            ->badge()
                            ->formatStateUsing(fn(int $state) => str_repeat('⭐', $state))
                            ->color(fn(int $state) => match (true) {
                                $state >= 4 => 'success',
                                $state === 3 => 'warning',
                                default => 'danger',
                            }),

                        TextEntry::make('created_at')
                            ->label('Reviewed At')
                            ->dateTime(),
                    ]),

                Section::make('User')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('Name'),
                        TextEntry::make('user.email')->label('Email')->copyable(),
                    ]),

                Section::make('Meal')
                    ->icon('heroicon-o-cake')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('meal.name')->label('Meal'),
                        TextEntry::make('meal.avg_rating')
                            ->label('Current Avg Rating')
                            ->formatStateUsing(fn($state) => number_format((float) $state, 2)),
                    ]),

                Section::make('Order')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        TextEntry::make('order_id')
                            ->label('Order #')
                            ->formatStateUsing(fn($state) => "#{$state}"),
                    ]),

                Section::make('Comment')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        TextEntry::make('comment')
                            ->hiddenLabel()
                            ->placeholder('No comment left.'),
                    ]),
            ]);
    }
}
