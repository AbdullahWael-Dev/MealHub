<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class MealInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ===== Header: image + name + key badges (full width) =====
                Section::make()
                    ->columnSpanFull()
                    ->components([
                        Grid::make(['sm' => 1, 'md' => 12])
                            ->components([
                                ImageEntry::make('display_image.image_path')
                                    ->label('')
                                    ->disk('public')
                                    ->circular()
                                    ->size(110)
                                    ->columnSpan(['sm' => 12, 'md' => 2]),

                                Grid::make(1)
                                    ->columnSpan(['sm' => 12, 'md' => 10])
                                    ->components([
                                        Grid::make(['sm' => 1, 'md' => 2])
                                            ->components([
                                                TextEntry::make('name')
                                                    ->size(TextSize::Large)
                                                    ->weight('bold'),

                                                TextEntry::make('category.name')
                                                    ->label('Category')
                                                    ->badge()
                                                    ->color('gray')
                                                    ->icon('heroicon-o-tag'),
                                            ]),

                                        Grid::make(['sm' => 2, 'md' => 4])
                                            ->components([
                                                TextEntry::make('is_available')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->formatStateUsing(fn($state) => $state ? 'Available' : 'Unavailable')
                                                    ->color(fn($state) => $state ? 'success' : 'danger')
                                                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),

                                                TextEntry::make('is_featured')
                                                    ->label('Featured')
                                                    ->badge()
                                                    ->formatStateUsing(fn($state) => $state ? 'Featured' : 'Standard')
                                                    ->color(fn($state) => $state ? 'warning' : 'gray')
                                                    ->icon(fn($state) => $state ? 'heroicon-o-star' : null),

                                                TextEntry::make('avg_rating')
                                                    ->label('Rating')
                                                    ->badge()
                                                    ->color('amber')
                                                    ->icon('heroicon-o-star')
                                                    ->formatStateUsing(fn($state, $record) => number_format($state, 1) . " ({$record->review_count})"),

                                                TextEntry::make('final_price')
                                                    ->label('Final Price')
                                                    ->badge()
                                                    ->color('success')
                                                    ->money('USD'),
                                            ]),
                                    ]),
                            ]),
                    ]),

                // ===== Description =====
                Section::make('Description')
                    ->icon('heroicon-o-document-text')
                    ->compact()
                    ->components([
                        TextEntry::make('description')
                            ->label('')
                            ->placeholder('No description provided.')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Grid::make(['sm' => 1, 'md' => 2])
                    ->components([
                        Section::make('Pricing')
                            ->icon('heroicon-o-currency-dollar')
                            ->iconColor('success')
                            ->components([
                                TextEntry::make('price')
                                    ->label('Original Price')
                                    ->money('USD')
                                    ->weight('medium'),

                                TextEntry::make('discount_price')
                                    ->label('Discounted Price')
                                    ->money('USD')
                                    ->placeholder('No discount')
                                    ->color('success')
                                    ->weight('medium'),
                            ]),

                        Section::make('Inventory')
                            ->icon('heroicon-o-archive-box')
                            ->iconColor('info')
                            ->components([
                                TextEntry::make('stock_quantity')
                                    ->label('Stock Quantity')
                                    ->badge()
                                    ->color(fn($state) => match (true) {
                                        $state <= 0 => 'danger',
                                        $state < 10 => 'warning',
                                        default => 'success',
                                    }),

                                TextEntry::make('preparation_time')
                                    ->label('Preparation Time')
                                    ->placeholder('—')
                                    ->formatStateUsing(fn($state) => $state ? "{$state} minutes" : null)
                                    ->icon('heroicon-o-clock')
                                    ->color('gray'),
                            ]),
                    ]),
                Section::make()
                    ->columnSpanFull()
                    ->compact()
                    ->components([
                        Grid::make(['sm' => 1, 'md' => 3])
                            ->components([
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->copyable()
                                    ->color('gray')
                                    ->icon('heroicon-o-link'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->since()
                                    ->color('gray')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Updated')
                                    ->since()
                                    ->color('gray')
                                    ->icon('heroicon-o-arrow-path'),
                            ]),
                    ]),
            ]);
    }
}
