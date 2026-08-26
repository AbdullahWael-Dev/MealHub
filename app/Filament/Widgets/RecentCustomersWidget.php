<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected static ?string $heading = 'Recent Customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email')->copyable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since(),
            ])->striped();
    }
}