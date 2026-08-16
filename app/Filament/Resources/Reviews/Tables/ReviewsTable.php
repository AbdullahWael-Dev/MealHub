<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Meal;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use App\Services\V1\ReviewServices\ReviewService as ReviewServicesReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('meal.name')
                    ->label('Meal')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_id')
                    ->label('Order')
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state) => str_repeat('⭐', $state))
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Comment')
                    ->limit(50)
                    ->searchable()
                    ->placeholder('—')
                    ->tooltip(fn ($record) => $record->comment),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 ⭐',
                        2 => '2 ⭐',
                        3 => '3 ⭐',
                        4 => '4 ⭐',
                        5 => '5 ⭐',
                    ]),

                SelectFilter::make('meal_id')
                    ->label('Meal')
                    ->options(fn () => Meal::query()->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn () => User::query()->pluck('name', 'id'))
                    ->searchable(),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Review $record) => route('filament.admin.resources.reviews.view', $record)),
                self::deleteAction(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('This will permanently delete the review and recalculate the meal rating.')
            ->action(function (Review $record) {
                app(ReviewServicesReviewService::class)->deleteReview($record->user, $record->id);
                Notification::make()
                    ->title('Review deleted and meal rating recalculated.')
                    ->success()
                    ->send();
            });
    }
}