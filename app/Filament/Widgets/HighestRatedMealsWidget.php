<?php

namespace App\Filament\Widgets;

use App\Models\Meal;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HighestRatedMealsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected static ?string $heading = 'Highest Rated Meals';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Meal::query()
                    ->where('review_count', '>', 0)
                    ->orderByDesc('avg_rating')
                    ->orderByDesc('review_count')
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Meal'),
                TextColumn::make('avg_rating')
                    ->label('Rating')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2).' ⭐'),

                TextColumn::make('review_count')
                    ->label('Reviews'),
            ]);
    }
}