<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages;
use App\Filament\Resources\Reviews\Schemas\ReviewInfolist;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Review;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReviewInfolist::configure($schema);
    }

    // Admin can't create reviews — they belong to real customer orders
    public static function canCreate(): bool
    {
        return false;
    }

    // Admin can't edit rating/comment — it's the customer's opinion, moderation only
    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
        ];
    }
}