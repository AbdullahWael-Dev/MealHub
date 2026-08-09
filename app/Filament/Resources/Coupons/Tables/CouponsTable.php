<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
     public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'percent',
                        'success' => 'fixed',
                    ]),

                TextColumn::make('value')
                    ->formatStateUsing(fn ($record) => $record->type === 'percent' ? $record->value.'%' : number_format($record->value, 2)),

                TextColumn::make('min_order_amount')
                    ->label('Min Order')
                    ->numeric(2),

                TextColumn::make('max_discount')
                    ->label('Max Discount')
                    ->numeric(2)
                    ->placeholder('—'),

                TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(fn ($record) => "{$record->used_count} / {$record->usage_limit}"),

                IconColumn::make('is_active')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
                SelectFilter::make('type')
                    ->options([
                        'percent' => 'Percent',
                        'fixed' => 'Fixed',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
